<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ProductHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    protected $productHelper;

    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
    }

    public function getcollection()
    {
        $Collections = Collection::all();
        $Collections->each(function ($collection) {
            $collection->media_urls = $collection->getMedia('collection')->map(function ($media) {
                return url('storage/' . $media->id . '/' . $media->file_name);
            });
        });
        return response()->json($Collections);
    }
    public function create(Request $request)
    {
        $validatedata = $request->validate([
            'title' => 'required|string',
            'headerColor' => 'nullable|string|size:7',
            'description' => 'nullable|string',
            'discount' => 'nullable|integer|min:0|max:100',
        ]);

        $Collection = Collection::create($validatedata);

        $Collection->addMultipleMediaFromRequest(['imageurl'])->each(function ($fileAdder) {
            $fileAdder->toMediaCollection('collection');
        });
        return response()->json([
            'message' => "create succefully"
        ]);
    }

    public function deletecollection(Collection $collection)
    {
        $collection->clearMediaCollection('collection');
        $collection->delete();
        return response()->json(['message' => 'Collection deleted successfully']);
    }

    public function singlecollection(Collection $collection, Request $request)
    {
        $user = auth('sanctum')->user();
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

        $ProductQuery = $collection->products();

        $products = $ProductQuery
            ->with(['Maincategory:id,name', 'Category:id,name', 'Subcategory:id,name', 'eligibleStatuses'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryids, fn($query) => $query->searchmain($maincategoryids))
            ->when($categoryids, fn($query) => $query->searchcategory($categoryids))
            ->when($subcategoryids, fn($query) => $query->searchsubcategory($subcategoryids))
            ->when($section, fn($query) => $query->section($user, $section))
            ->when($price1 || $price2, fn($query) => $query->price($price1, $price2))
            ->when($sizes, fn($query) => $query->size($sizes))
            ->when($colors, fn($query) => $query->color($colors))
            ->when($brands, fn($query) => $query->brand($brands))
            ->where('active', 1)
            ->paginate(16);


        $this->productHelper->transform($products, $user);



        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'description' => $collection->description,
                'discount' => $collection->discount,
                'created_at' => $collection->created_at,
                'updated_at' => $collection->updated_at,
                'products' => ProductResource::collection($products, $user),
            ]
        ]);
    }





    public function addtocollection(Collection $collection, Product $product)
    {
        $collection->products()->attach($product->id);
        return response()->json(['message' => 'Product added to collection successfully']);
    }


    public function singleadmincollection(Collection $collection)
    {
        $collection->load('products');

        $collection->products->transform(function ($product) {

            $product->discountstatusprice = $product->discountprice;
            return $product;
        });

        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'title' => $collection->title,
                'headerColor' => $collection->headerColor,
                'description' => $collection->description,
                'discount' => $collection->discount,
                'created_at' => $collection->created_at,
                'updated_at' => $collection->updated_at,
                'products' => ProductResource::collection($collection->products),
            ]
        ]);
    }

    public function allProductsForCollection(Collection $collection)
    {
        $products = Product::where('discount', $collection->discount)->get();
        return ProductResource::collection($products);
    }
}
