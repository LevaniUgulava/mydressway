<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Helpers\EnumHelper;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\CssSelector\Node\FunctionNode;

class OrderController extends Controller
{

    public function getorder()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('products')->get()->map(function ($order) {
            $order->products = $order->products->map(function ($product) {
                $productModel = Product::find($product->id);

                $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                });

                return $product;
            });

            return $order;
        });
        $groupedOrders = [];
        $groupedStatus = [];
        foreach ($orders as $order) {
            $products = [];

            foreach ($order->products as $product) {
                $products[] = [
                    'name' => $product->name,
                    'quantity'     => $product->pivot->quantity,
                    'size'         => $product->pivot->size,
                    'retail_price' => $product->pivot->retail_price,
                    'total_price'  => $product->pivot->total_price,
                    'image_urls' => $product->image_urls
                ];
            }

            $groupedOrders[] = [
                'order_id' => $order->id,
                'order_amount' => $order->amount_paid,
                'order_status' => $order->status,
                'products' => $products
            ];

            $groupedStatus[$order->status][] = end($groupedOrders);
        }

        return response()->json($groupedStatus);
    }

    public function getadminorder()
    {

        $orders = Order::with('user')->orderby('created_at', 'desc')->get()->map(function ($order) {

            $order->products = $order->products->map(function ($product) {
                $productModel = Product::find($product->id);

                $product->image_urls = $productModel->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                });

                return $product;
            });
            return $order;
        });

        $groupedOrders = [];
        foreach ($orders as $order) {
            $products = [];

            foreach ($order->products as $product) {
                $products[] = [
                    'name' => $product->name,
                    'quantity'     => $product->pivot->quantity,
                    'size'         => $product->pivot->size,
                    'retail_price' => $product->pivot->retail_price,
                    'total_price'  => $product->pivot->total_price,
                    'image_urls' => $product->image_urls
                ];
            }
            $groupedOrders[] = [
                'order_id' => $order->id,
                'order_amount' => $order->amount_paid,
                'order_status' => $order->status,
                'products' => $products,
                'user' => $order->user,

            ];
        }

        return response()->json([
            'orders' => $groupedOrders,
        ]);
    }



    public function orderstatus(Request $request)
    {
        $status = $request->input('status');
        $ids = $request->input('id');
        Order::whereIn('id', $ids)->update(['status' => $status]);
        return response()->json(['message' => 'Order statuses updated successfully'], 200);
    }
    public function singleadminorder($id)
    {
        $order = Order::with('user', 'products')->orderBy('created_at', 'desc')->findOrFail($id);

        $order->products = $order->products->map(function ($product) {
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });

            return $product;
        });

        return response()->json([
            'order' => $order,
        ]);
    }
}
