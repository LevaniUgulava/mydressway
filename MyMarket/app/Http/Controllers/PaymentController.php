<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Events\ProductPurchased;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PaymentController extends Controller
{
    protected $discountController;


    public function __construct(DiscountController $discountController)
    {
        $this->discountController = $discountController;
    }

    public function Temporder(Request $request)
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
            "products.*.quantity" => "required|integer",
            "products.*.product_id" => "required|numeric",
            "products.*.type" => "required|string|max:100",
            "products.*.name" => "required|string|max:100",
            "products.*.color" => "required|string|max:100",
            "products.*.size" => "required",
            "products.*.retail_price" => "required|numeric",
            "products.*.total_price" => "required|numeric",
        ]);


        $usertemp = $user->fresh()->usertemp;
        $totalPrice = 0;

        foreach ($request->products as $item) {

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
                    'retail_price' => $item['retail_price'],
                    'total_price' => $item['total_price']
                ]
            );
            $totalPrice += $item['total_price'];
        }

        $usertemp->update([
            'total_price' => $totalPrice
        ]);

        return response()->json([], 200);
    }

    public function getTemporder()
    {
        $user = auth()->user();

        if (!$user->usertemp) {
            return response()->json(["orders" => [], "total_price" => 0, "promocode" => null, "payment" => null]);
        }

        $usertemp = $user->usertemp;
        $tempOrders = $usertemp->temporders;
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




    public function checkout(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone' => 'nullable|regex:/^[1-9][0-9]{8}$/',
            'firstname' => 'nullable|string|max:100',
            'address_id' => 'required',
            'lastname' => 'nullable|string|max:100',
            'products' => 'required|array',
            'products.*.product_id' => 'nullable|integer',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.size' => 'required',
            "products.*.color" => "required|string|max:100",
            'products.*.retail_price' => 'required|numeric',
            'products.*.total_price' => 'required|numeric',
        ]);
        $firstname = $request->input('firstname');
        $lastname = $request->input('lastname');
        $number = $request->input('phone');
        $address_id = $request->input('address_id');
        $gets = collect($request->input('products'));
        $price = 0;

        foreach ($gets as $get) {
            $price += $get['total_price'];
        }
        $order = Order::create([
            'user_id' => $user->id,
            'amount_paid' => $price,
            'address_id' => $address_id,
            'status' => Status::Pending,
            'fullname' => $firstname . " " . $lastname,
            "number" => $number,
        ]);


        if ($order) {

            foreach ($gets as $item) {

                DB::table('order_item')->insert([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
                    'retail_price' => $item['retail_price'],
                    'total_price' => $item['total_price'],
                    "color" => $item["color"],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $i = $this->managequantity($item['product_id'], $item['size'], $item["color"], $item['quantity']);
            }


            if ($user && $get['type'] === "cart") {
                $cart = $user->cart;
                if (!$cart) {
                    return response()->json(['message' => 'No active cart available for checkout'], 404);
                }
                $cart->products()->detach();
                $cart->delete();
            }
            $this->updatetotalspent($user, $price);
            $this->checkoutmail($gets, $user);
            return response()->json($i);
        }
        return response()->json(['message' => 'Order creation failed'], 500);
    }


    private function updatetotalspent($user, $price)
    {
        $user->total_spent += $price;
        $user->save();
        $this->updatestatus($user);
        return $user->total_spent;
    }

    public function updatestatus($user)
    {
        $totalSpent = $user->total_spent;


        $status = Userstatus::where('toachieve', '<=', $totalSpent)
            ->orderBy('toachieve', 'desc')
            ->first();

        if ($status) {
            $user->userstatus_id = $status->id;
            $user->save();
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
            $colorsize->delete();
        } else {
            $colorsize->update(["quantity" => $newquantity]);
        }

        $sizeExist = Color::whereHasMorph("colorable", [Shoessize::class, Clothsize::class], function ($q) use ($size, $id) {
            $q->where("size", $size)
                ->where("product_id", $id);
        })->exists();

        if (!$sizeExist) {
            Shoessize::where("size", $size)->where("product_id", $id)->delete();
            Clothsize::where("size", $size)->where("product_id", $id)->delete();
        }

        $otherSizesExist = Shoessize::where("product_id", $id)->exists() || Clothsize::where("product_id", $id)->exists();

        $product = Product::find($id);
        if (!$otherSizesExist && $product) {
            $product->update(["active" => 0]);
        }
    }

    public function checkoutmail($orders, $user)
    {
        $mappedOrder = $orders->map(function ($order) {
            $product = Product::find($order['product_id']);
            $image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl())->toArray();  // Convert to an array of URLs
            return [
                'product_id' => $order['product_id'],
                'name' => $product->name,
                'quantity' => $order['quantity'],
                'size' => $order['size'],
                'retail_price' => $order['retail_price'],
                'total_price' => $order['total_price'],
                'color' => $order['color'],
                'product_image' => $image_urls,
            ];
        });


        $user->notify(new ProductPurchaseNotification($mappedOrder));
    }
}
