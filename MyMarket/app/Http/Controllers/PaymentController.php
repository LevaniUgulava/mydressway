<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Events\ProductPurchased;
use App\Helpers\ProductHelper;
use App\Listeners\SendProducutPurchasedNotification;
use App\Models\Cart;
use App\Models\Clothsize;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\Shoessize;
use App\Models\TemporaryOrder;
use App\Models\User;
use App\Models\Userstatus;
use App\Notifications\ProductPurchaseNotification;
use Google\Service\CloudBuild\Probe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Decimal;

class PaymentController extends Controller
{
    protected $discountController;


    public function __construct(DiscountController $discountController)
    {
        $this->discountController = $discountController;
    }

    public function Temporder(Request $request, ProductHelper $productHelper)
    {
        $user = auth()->user();

        if (!$user->usertemp) {
            $user->usertemp()->create([
                "payment" => null,
                "expire_at" => now()->addDay(),
                "total_price" => 0,
                "promocode_price" => 0,

            ]);
        }

        $request->validate([
            "products" => "required|array",
            'products.*.seeOriginal' => "nullable",
            "products.*.quantity" => "required|integer",
            "products.*.product_id" => "required|numeric",
            "products.*.type" => "required|string|max:100",
            "products.*.name" => "required|string|max:100",
            "products.*.color" => "required|string|max:100",
            "products.*.size" => "required",
        ]);


        $usertemp = $user->fresh()->usertemp;
        $totalPrice = 0;

        foreach ($request->products as $item) {
            $prices = $this->getPrices($user, $item['product_id'], $item['quantity'], $item['seeOriginal'], $productHelper);


            TemporaryOrder::firstOrCreate(
                [
                    'usertemp_id' => $usertemp->id,
                    'product_id' => $item['product_id'],
                    'size' => $item['size'],
                ],
                [
                    'quantity' => $item['quantity'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'color' => $item['color'],
                    'isOriginal' => $item['seeOriginal'],
                    'retail_price' => $prices['retail_price'],
                    'total_price' => $prices['total_price']
                ]
            );
            $totalPrice += $prices['total_price'];
        }

        $usertemp->update([
            'total_price' => $totalPrice
        ]);

        return response()->json([], 200);
    }
    private function getPrices($user, $product_id, $quantity, $seeOriginal, $productHelper)
    {
        $product = Product::findorfail($product_id);
        if ($seeOriginal) {
            $product->price = $product->price;
        } else {
            $productHelper->getDiscountPrice($product, $user);
            $product->price = $product->discountprice;
        }

        $total_price = $quantity * $product->price;
        return [
            'retail_price' => $product->price,
            'total_price' => $total_price
        ];
    }

    public function getTemporder()
    {
        $user = auth()->user();

        if (!$user->usertemp) {
            return response()->json(["orders" => [], "total_price" => 0, "promocode" => null, "payment" => null]);
        }


        $usertemp = $user->usertemp;
        $tempOrders = $usertemp->temporders()->with(['product' => function ($query) {
            $query->select('id');
        }])->get();


        $payment = $usertemp->payment;
        $promocode = $usertemp->promocode;

        if ($promocode) {
            $price = $usertemp->promocode_price;
        } else {
            $price = $usertemp->total_price;
        }

        return response()->json([
            "orders" => $tempOrders,
            "promocode" => $promocode,
            "payment" => $payment,
            "total_price" => $price
        ]);
    }

    public function changePayment()
    {
        $user = auth()->user();

        if (!$user->usertemp) {
            return response()->json(["orders" => [], "total_price" => 0, "promocode" => null, "payment" => null]);
        }
        try {
            $user->usertemp->update([
                "promocode" => null,
                "payment" => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An error occurred while updating payment details.",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function deleteTempOrder()
    {
        $user = auth()->user();
        if ($user) {
            $deleted = $user->usertemp
                ->delete();
        } else {
            return response()->json(['error' => 'User or guest token is required'], 400);
        }
        if ($deleted) {
            return response()->json(["message" => "Temporary order deleted"]);
        } else {
            return response()->json(["message" => "No temporary order found"], 404);
        }
    }




    public function checkout(Request $request, ProductHelper $ph)
    {
        $user = Auth::user();
        $products = $user->usertemp->temporders()->get(['product_id', 'quantity', 'size', 'color', 'retail_price', 'total_price', 'isOriginal']);
        $request->validate([
            'phone' => 'nullable|regex:/^[1-9][0-9]{8}$/',
            'firstname' => 'nullable|string|max:100',
            'address_id' => 'required',
            'lastname' => 'nullable|string|max:100',
        ]);

        $firstname = $request->input('firstname');
        $lastname = $request->input('lastname');
        $number = $request->input('phone');
        $address_id = $request->input('address_id');


        $response = $this->checkLimit($user, $products);
        if (!$response['status']) {
            return response()->json([
                'key' => 'statusLimit',
            ], 409);
        }



        $price = 0;
        $type = $products->first()['type'] ?? null;

        $price = $products->sum('total_price');

        $order = Order::create([
            'user_id' => $user->id,
            'amount_paid' => $price,
            'address_id' => $address_id,
            'status' => Status::Pending,
            'fullname' => $firstname . " " . $lastname,
            "number" => $number,
        ]);


        if ($order) {

            foreach ($products as $item) {

                $order->products()->attach($item['product_id'], [
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
                    'retail_price' => $item['retail_price'],
                    'total_price' => $item['total_price'],
                    'color' => $item['color'],
                ]);

                $i = $this->managequantity($item['product_id'], $item['size'], $item["color"], $item['quantity']);
            }


            if ($user && $type === "cart") {
                $cart = $user->cart;
                if (!$cart) {
                    return response()->json(['message' => 'No active cart available for checkout'], 404);
                }
                $cart->products()->detach();
                $cart->delete();
            }
            $this->updatetotalspent($user, $price, $ph);

            if ($response['status']) {
                $this->deductLimit($user, $response['value']);
            }

            $this->checkoutmail($products, $user);

            return response()->json($i);
        }
        return response()->json(['message' => 'Order creation failed'], 500);
    }
    private function deductLimit($user, $total)
    {
        return DB::transaction(function () use ($user, $total) {
            $statusInfo = $user->userstatusinfo()->lockForUpdate()->first();
            $statusInfo->left = round((float) $statusInfo->left - $total, 2);
            $statusInfo->save();

            return $statusInfo->left;
        });
    }


    private function checkLimit($user, $products)
    {
        $nonOriginal = $products
            ->filter(fn($product) => $product->isOriginal == 0)
            ->pluck('total_price');

        $total = $nonOriginal->sum();
        $limit = (float) $user->userstatusinfo->left;

        return [
            'value' => round($total, 2),
            'status' => $total <= $limit,
        ];
    }



    private function updatetotalspent($user, $price, $ph)
    {
        $user->total_spent += $price;
        $user->save();
        $this->updatestatus($user, $ph);
        return $user->total_spent;
    }

    private function updatestatus($user, $ph)
    {
        $totalSpent = $user->total_spent;

        $statusInfo = $user->userstatusinfo;

        $status = Userstatus::where('toachieve', '<=', $totalSpent)
            ->orderBy('toachieve', 'desc')
            ->first();
        if (!$statusInfo || $statusInfo->userstatus_id !== $status->id) {
            $user->userstatusinfo()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'userstatus_id'   => $status->id,
                    'left'            => $status->limit,
                    'userstatus_time' => now(),
                    'end_time'        => $ph->addDuration(now(), $status->expansion, $status->time),
                ]
            );
        }

        return response()->json($status);
    }


    private function managequantity($id, $size, $color, int $quantity)
    {
        $colorsize = Color::where("color", $color)
            ->whereHasMorph("colorable", [Shoessize::class, Clothsize::class], function ($q) use ($size, $id) {
                $q->where("size", $size)
                    ->where("product_id", $id);
            })
            ->first();

        if (!$colorsize) {
            return response()->json(["error" => "Color and size combination not found"], 404);
        }

        $newquantity = $colorsize->quantity - $quantity;

        if ($newquantity < 1) {
            $colorableType = class_basename($colorsize->colorable_type);
            $colorsize->delete();
        } else {
            $colorsize->update(["quantity" => $newquantity]);
        }

        $sizeExist = Color::whereHasMorph("colorable", [Shoessize::class, Clothsize::class], function ($q) use ($size, $id) {
            $q->where("size", $size)
                ->where("product_id", $id);
        })->exists();

        if (!$sizeExist) {
            if ($colorableType === 'Shoessize') {
                Shoessize::where('size', $size)->where('product_id', $id)->delete();
            } elseif ($colorableType === 'Clothsize') {
                Clothsize::where('size', $size)->where('product_id', $id)->delete();
            }
        }

        $otherSizesExist = Shoessize::where("product_id", $id)->exists() || Clothsize::where("product_id", $id)->exists();

        $product = Product::find($id);
        if (!$otherSizesExist && $product) {
            $product->delete();
        }
    }

    public function checkoutmail($orders, $user)
    {
        $mappedOrder = $orders->map(function ($order) {
            $product = Product::withTrashed()->find($order['product_id']);
            $image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl())->toArray();

            return [
                'product_id' => $order['product_id'],
                'name' => $product->name,
                'quantity' => $order['quantity'],
                'size' => $order['size'],
                'retail_price' => $order['retail_price'],
                'total_price' => $order['total_price'],
                'color' => $order['color'],
                'product_image' => $image_urls[0],
            ];
        });

        $data = [
            'to_email' => $user->email,
            'to_name' => "Recipt",
            'order_items' => [
                'first_name' => $user->name,
                'last_name' => $user->surname,
                'order_id' => 'ORD-' . now()->format('YmdHis'),
                'total' => '₾' . $mappedOrder->sum('total_price'),
                'items' => $mappedOrder
            ],
            'template_key' => "order",
        ];

        event(new ProductPurchased((object) $data));
    }
}
