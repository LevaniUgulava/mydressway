<?php

namespace App\Http\Controllers;

use App\Enums\ProductSize;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DiscountNotification;
use App\Repository\Product\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    public $productRepository;
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function display(Request $request)
    {
        $user = auth('api')->user();
        $name = $request->query('searchname', '');
        $maincategoryids = $request->query('maincategory', '');
        $categoryids = $request->query('category', '');
        $subcategoryids = $request->query('subcategory', '');
        $section = (array)$request->query('section', []);
        $price1 = $request->query('min', '');
        $price2 = $request->query('max', '');
        $sizes = $request->query('sizes');
        $colors = $request->query('colors');
        $brands = $request->query('brands');
        $pagination = $request->get('perPage', 16);


        $result = [];
        foreach ($section as $s) {
            $products = $this->productRepository->display($name, $maincategoryids, $categoryids, $subcategoryids, $pagination, $user, $s, null, $price1, $price2, $colors, $sizes, $brands);
            $result[$s] = [
                'data' => $products->map(fn($product) => new ProductResource($product, $user)),
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                ],
                'links' => [
                    'first' => $products->url(1),
                    'last' => $products->url($products->lastPage()),
                    'next' => $products->nextPageUrl(),
                    'prev' => $products->previousPageUrl(),
                ]
            ];
        }

        return response()->json($result);
    }



    public function admindisplay(Request $request)
    {
        $name = $request->query('searchname', '');
        $maincategoryid = $request->query('maincategory', '');
        $categoryid = $request->query('category', '');
        $subcategoryid = $request->query('subcategory', '');
        $pagination = $request->query('perPage', 12);

        $products = $this->productRepository->admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination);
        return ProductResource::collection($products);
    }

    public function notactive($id)
    {
        $this->productRepository->notactive($id);
        return response()->json(['message' => 'yes']);
    }
    public function active($id)
    {
        $this->productRepository->active($id);
        return response()->json(['message' => 'yes']);
    }

    public function displaybyid($id)
    {
        $user = auth('api')->user();
        $product = $this->productRepository->displaybyid($id, $user);
        return new ProductResource($product);
    }

    public function similarproducts($id)
    {
        $user = auth('api')->user();
        $products = $this->productRepository->similarproducts($id, $user);
        return ProductResource::collection($products);
    }

    public function create(Request $request)
    {
        try {
            $check = $this->productRepository->create($request);

            if (!$check) {
                return response()->json([
                    'message' => 'Something went wrong!!',
                ], 500);
            }

            return response()->json([
                'message' => 'Added Successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Product creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function setdiscount(Request $request)
    {

        $ids = $request->id;
        $discount = $request->discount;

        foreach ($ids as $id) {
            $product = Product::findOrFail($id);
            $price = $product->price;
            $product->update([
                "discount" => $discount,
                "discountprice" => $price * ((100 - $discount) / 100)
            ]);
        }
        // if ($request->send) {
        // if (true) {
        //     $users = User::get();
        //     foreach ($users as $user) {
        //         $user->notify(new DiscountNotification($ids, $user->name, $discount));
        //     }
        //     $this->discountproducts($ids);
        // }

        return response()->json("success");
    }



    public function discountproducts($ids)
    {
        if (empty($ids)) {
            return response()->json("No discounted products found", 404);
        }

        $products = Product::whereIn('id', $ids)->get();

        return response()->json($products);
    }


    public function getSizes()
    {
        return response()->json([
            "letterbased" => ProductSize::letterBasedSizes(),
            "numericbased" => ProductSize::numericBasedSizes()
        ]);
    }
    public function getNameforSearch()
    {
        $productNames = Product::select('name')->distinct()->inRandomOrder()->pluck('name');
        return response()->json($productNames);
    }
}
