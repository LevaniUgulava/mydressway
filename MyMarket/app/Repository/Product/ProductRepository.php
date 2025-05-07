<?php

namespace App\Repository\Product;

use App\Helpers\ProductHelper;
use App\Helpers\Translator;
use App\Models\Clothsize;
use App\Models\Product;
use App\Models\Shoessize;
use App\Repository\Brand\BrandRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{
    private $productHelper;
    protected $brandRepository;
    public function __construct(ProductHelper $productHelper, BrandRepositoryInterface $brandRepository)
    {
        $this->productHelper = $productHelper;
        $this->brandRepository = $brandRepository;
    }
    public function display($name, $maincategoryids, $categoryids, $subcategoryids, $pagination, $user, $section, $lang, $price1, $price2, $colors, $sizes, $brands)
    {

        $products = Product::query()->withAvg('rateproduct', 'rate')
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
            ->paginate($pagination);

        $this->productHelper->transform($products, $user, $lang);

        return $products;
    }



    public function admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination)
    {
        $products = Product::with(['Maincategory', 'Category', 'Subcategory', 'user'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryid, fn($query) => $query->searchmain($maincategoryid))
            ->when($categoryid, fn($query) => $query->searchcategory($categoryid))
            ->when($subcategoryid, fn($query) => $query->searchsubcategory($subcategoryid))
            ->paginate($pagination);;

        $products->getCollection()->transform(function ($product) {
            $product->image_urls = $product->getMedia('default')->map(function ($media) {
                return $media->getUrl();
            });
            return $product;
        });

        return $products;
    }




    public function notactive($id)
    {
        $product = Product::findorfail($id);
        $product->update([
            'active' => 0,
        ]);
    }

    public function active($id)
    {
        $product = Product::findorfail($id);
        $product->update([
            'active' => 1,
        ]);
    }


    public function displaybyid($id, $user)
    {
        $product = Product::with('shoesize', 'clothsize')
            ->withAvg('rateproduct', 'rate')
            ->where('id', $id)
            ->first();
        $this->productHelper->transformSingleProduct($product, $user);
        return $product;
    }
    public function similarproducts($id, $user)
    {
        $product = Product::where('id', $id)->first();

        $categoryid = $product->category_id;

        $products = Product::where('id', '!=', $id)
            ->where('category_id', $categoryid)
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $this->productHelper->transform($products, $user, null, false);
        return $products;
    }

    public function create(Request $request)
    {

        try {

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'discountprice' => $request->price,
                'maincategory_id' => $request->maincategory_id,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'additionalinfo' => $request->additionalinfo,
            ]);

            $sizes = $request->input('size', []);
            $quantities = $request->input('quantity', []);
            $colors = $request->input('color', []);
            if ($request->size_type === "numeric") {
                foreach ($sizes as $index => $size) {
                    $numeric_size = Shoessize::create([
                        'size' => $size,
                        'product_id' => $product->id,
                    ]);
                    foreach ($colors[$index] as $colorindex => $color) {
                        $numeric_size->colors()->create([
                            'color' => $color,
                            'quantity' => $quantities[$index][$colorindex],
                        ]);
                    }
                }
            } elseif ($request->size_type === "letter-based") {

                foreach ($sizes as $index => $size) {
                    $cloth_size = Clothsize::create([
                        'size' => $size,
                        'product_id' => $product->id,
                    ]);
                    foreach ($colors[$index] as $colorindex => $color) {
                        $cloth_size->colors()->create([
                            'color' => $color,
                            'quantity' => $quantities[$index][$colorindex],
                        ]);
                    }
                }
            }
            $product->addMultipleMediaFromRequest(['images'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection();
                });

            $this->brandRepository->ProductRelation($product->id, $request->brand_id);

            return true;
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());
            dd($e->getMessage());
            return  false;
        }
    }
}
