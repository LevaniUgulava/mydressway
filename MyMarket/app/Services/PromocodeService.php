<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\TemporaryOrder;
use App\Repository\Promocode\PromocodeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromocodeService
{
    protected $promocodeRepository;

    public function __construct(PromocodeRepositoryInterface $promocodeRepository)
    {
        $this->promocodeRepository = $promocodeRepository;
    }
    public function applytoPromocode(Request $request)
    {
        $user = Auth::user();
        $promocode = $request->input("promocode");
        $type = $request->input("type");


        $check = $this->promocodeRepository->getbyName($promocode);
        if (!$check) {
            return response()->json('ვერ მოძებნეთ ამ პრომოკოდით მონაცემები', 400);
        }

        if ($check->expires_at && $check->expires_at < now()) {
            return response()->json('პრომოკოდის ვადა ამოიწურა', 400);
        }
        if ($check->type !== $type) {
            return response()->json('ვერა', 400);
        }

        if ($check->usage_quantity !== null) {
            $this->calculateUsage($user, $check);
        }
        $temporder = $user->usertemp->temporders;
        $result = $this->analyseProduct($user, $check, $temporder);

        $price = $user->usertemp->total_price;
        $new_price = $price - $result;

        if ($user->usertemp->promocode) {
            return response()->json("პრომოკოდი უკვე გამოყენებულია");
        }
        $user->usertemp()->update([
            "promocode" => $promocode,
            "payment" =>  $type,
            "promocode_price" => $new_price
        ]);

        return response()->json(true);
    }
    public function calculateUsage($user, $promocode)
    {
        $order = $user->orders->where("promocode", $promocode->name)->count();
        if ($order >= $promocode->usage_quantity) {
            return response()->json('თქვენ უკვე გამოიყენეთ ეს კოდი', 400);
        }
        return;
    }
    public function analyseProduct($user, $promocode, $temporder)
    {
        $category = $promocode->categories->pluck('id');
        $productIds = $temporder->pluck('product_id');
        $products = Product::whereIn('id', $productIds)
            ->whereIn('category_id', $category)
            ->get();

        $filteredOrders = $temporder->filter(function ($order) use ($products) {
            return $products->contains('id', $order->product_id);
        });

        $price = $filteredOrders->reduce(function ($carry, $item) {
            return $carry + $item->total_price;
        });
        $result = $this->calculatePromocodePrice($price, $promocode);


        return $result;
    }
    public function calculatePromocodePrice($price, $promocode)
    {
        if ($promocode->discount_percentage) {
            return $price * ($promocode->discount_percentage) / 100;
        } else {
            return $promocode->fixed_discount;
        }
    }
}
