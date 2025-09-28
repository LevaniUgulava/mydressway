<?php

namespace App\Http\Controllers\Profile;

use App\Enums\ProductSize;
use App\Helpers\ProductHelper;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Clothsize;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shoessize;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class CartController extends Controller
{
    protected $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function Like($id)
    {
        $user = Auth::user();

        if (!$user->manyproducts->contains($id)) {
            $user->manyproducts()->attach($id);
            return response()->json([
                'message' => 'liked',
            ]);
        } else {
            return response()->json([
                'message' => 'already like'
            ]);
        }
    }

    public function unLike($id)
    {
        $user = Auth::user();

        if ($user->manyproducts->contains($id)) {
            $user->manyproducts()->detach($id);
            return response()->json([
                'message' => 'unliked',
            ]);
        } else {
            return response()->json([
                'message' => 'already unlike'
            ]);
        }
    }


    public function cart(Product $product, Request $request)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json(["action" => 'cart'], 401);
        }

        $cart = $user->cart ?? Cart::create(['user_id' => $user->id, 'cart_total_price' => 0]);



        $cartItem = $cart->products()->where('product_id', $product->id)->where('size', $request->size)->first();
        $quantity = $request->quantity ?? 1;
        $size =  $request->size;
        $color =  $request->color;
        $isOriginal = $request->isOriginal;

        $totalPrice = $isOriginal ? $product->price * $quantity : $user->getPriceByStatus($product, $product->price, $product->discountprice) * $quantity;
        $retail_price = $isOriginal ? $product->price : $user->getPriceByStatus($product, $product->price, $product->discountprice);

        if ($cartItem) {
            return response()->json([
                'message' => 'პროდუქტი კალათაში დამატებულია'
            ]);
        } else {

            $cart->products()->attach($product->id, [
                'quantity' => $quantity,
                'size' => $size,
                "color" => $color,
                'isOriginal' => $isOriginal,
                'retail_price' => $retail_price,
                'total_price' => $totalPrice
            ]);
            return response()->json([
                'message' => 'პროდუქტი კალათაში დაემტა',
                'id' => $cart->products()->first()->pivot->id
            ]);
        }
    }
    public function changeSize($id, Request $request)
    {
        $user = Auth::user();
        $newsize = $request->newsize;

        $cartId = $user->cart->id;

        $updated = DB::table('cart_item')
            ->where('id', $id)
            ->where('cart_id', $cartId)
            ->update(['size' => $newsize, "color" => null]);

        return $updated
            ? response()->json(['message' => 'Size updated successfully'])
            : response()->json(['error' => 'Failed to update size'], 500);
    }



    public function changeColor($id, Request $request)
    {
        $user = Auth::user();
        $color = $request->color;
        $cartId = $user->cart->id;

        $updated = DB::table('cart_item')
            ->where('id', $id)
            ->where('cart_id', $cartId)
            ->update(['color' => $color]);

        if ($updated) {
            return response()->json(['message' => 'Color updated successfully']);
        } else {
            return response()->json(['error' => 'Failed to update color'], 500);
        }
    }


    public function getcart()
    {
        $user = Auth::user();
        $cart = $user->cart;


        $products = $cart->products()->get()->map(function ($product) use ($user) {
            $productModel = Product::find($product->id);

            $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });


            $product->size = $this->productService->getSizeData($product);

            unset($product->clothsize);

            $product->total_price = $product->pivot->total_price;

            return $product;
        });

        $totalPrice = $products->sum(function ($product) {
            return $product->pivot->total_price;
        });

        $cart->cart_total_price = $totalPrice;
        $cart->save();

        return response()->json(["products" => $products, 'totalPrice' => $totalPrice]);
    }

    public function quickUpdate(Request $request, ProductService $productService)
    {
        $user = Auth::user();
        $product_id = $request->id;
        $size = $request->size;
        $color = $request->color;
        $cart = $user->cart;
        $action = $request->action;
        $entity = $cart
            ->products()
            ->wherePivot('color', $color)
            ->wherePivot('size', $size)
            ->wherePivot('product_id', $product_id)
            ->first();
        if (!$entity) {
            return response()->json(['error' => 'Product not found in user orders'], 404);
        }
        $product = Product::find($entity->id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $fullquantity = $productService->getQuantity($product, $entity->pivot->size, $entity->pivot->color);

        $currentquantity = $entity->pivot->quantity;
        if ($action === 'increment' && $fullquantity > $currentquantity) {
            $currentquantity++;
        } elseif ($action === 'decrement' && $currentquantity > 1) {
            $currentquantity--;
        } elseif ($action === 'delete' && $currentquantity === 1) {
            $deleted = $cart->products()
                ->detach($product->id);
            if ($deleted) {
                return response()->json('Deleted Successfully');
            }
            return response()->json(['error' => 'Something went wrong'], 400);
        } else {
            return response()->json(['error' => 'Invalid action or quantity'], 400);
        }
        $newTotalPrice = $user->getPriceByStatus($product, $product->price, $product->discountprice) * $currentquantity;
        $updated = $cart->products()
            ->updateExistingPivot($product->id, [
                'quantity' => $currentquantity,
                'total_price' => $newTotalPrice
            ]);

        if ($updated) {
            $totalPrice = $cart->products->map(function ($product) {
                return $product->pivot->total_price;
            })->sum();

            $cart->update(['cart_total_price' => $totalPrice]);
            return response()->json([
                "quantity" => $currentquantity,
                'total_price' => $newTotalPrice,
                "cart_total_price" => $cart->cart_total_price
            ]);
        } else {
            return response()->json(['error' => 'Failed to update quantity'], 500);
        }
    }


    public function updatequantity($id, $action, ProductService $productService)
    {
        $user = Auth::user();
        $cart = $user->cart;
        $data = $cart->products()->wherePivot('id', $id)->first();

        if (!$data) {
            return response()->json(['error' => 'Product not found in user orders'], 404);
        }

        $product = Product::find($data->id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $fullquantity = $productService->getQuantity($product, $data->pivot->size, $data->pivot->color);


        $currentquantity = $data->pivot->quantity;
        if ($action === 'increment' && $fullquantity > $currentquantity) {
            $currentquantity++;
        } elseif ($action === 'decrement' && $currentquantity > 1) {
            $currentquantity--;
        } else {
            return response()->json(['error' => 'Invalid action or quantity'], 400);
        }

        $newTotalPrice = $user->getPriceByStatus($product, $product->price, $product->discountprice) * $currentquantity;

        $updated = DB::table('cart_item')
            ->where('cart_id', $cart->id)
            ->where('id', $id)
            ->update([
                'quantity' => $currentquantity,
                'total_price' => $newTotalPrice
            ]);

        if ($updated) {
            $totalPrice = $cart->products->map(function ($product) {
                return $product->pivot->total_price;
            })->sum();

            $cart->update(['cart_total_price' => $totalPrice]);
            return response()->json([
                "quantity" => $currentquantity,
                'total_price' => $newTotalPrice,
                "cart_total_price" => $cart->cart_total_price
            ]);
        } else {
            return response()->json(['error' => 'Failed to update quantity'], 500);
        }
    }






    public function deletecart($id)
    {
        $user = Auth::user();

        $cart = $user->cart()->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $deleted = $cart->products()->wherePivot('id', $id)->detach();

        if ($deleted) {
            return response()->json(["message" => "Product deleted from cart"]);
        } else {
            return response()->json(["error" => "Failed to delete product"], 500);
        }
    }


    // private function randSizeHandle($product, $type)
    // {
    //     $productexistsizes = $product->$type;
    //     $existsize = $productexistsizes->pluck('size')->toArray();
    //     return $existsize[array_rand($existsize)];
    // }

    // private function randColorHandle($size, $id)
    // {
    //     $colors = Color::wherehasmorph("colorable", [Shoessize::class, Clothsize::class], function ($q) use ($size, $id) {
    //         $q->where("size", $size)->where("product_id", $id);
    //     })->pluck("color")->toArray();

    //     return $colors[array_rand($colors)];
    // }
}
