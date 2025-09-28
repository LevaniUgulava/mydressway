<?php

namespace App\Repository\Product;

use App\Helpers\ProductHelper;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Brand;
use App\Models\Clothsize;
use App\Models\Product;
use App\Models\Shoessize;
use App\Repository\Brand\BrandRepositoryInterface;
use App\Services\ProductService;
use App\Services\SpuService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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


        $products = Product::query()
            ->with(['Maincategory:id,ka_name,en_name', 'Category:id,ka_name,en_name', 'Subcategory:id,ka_name,en_name', 'eligibleStatuses', 'brands:id,name'])
            ->when($name, fn($query) => $query->searchname($name))
            ->when($maincategoryids, fn($query) => $query->searchmain($maincategoryids))
            ->when($categoryids, fn($query) => $query->searchcategory($categoryids))
            ->when($subcategoryids, fn($query) => $query->searchsubcategory($subcategoryids))
            ->when($section, fn($query) => $query->section($user, $section))
            ->when($price1 || $price2, fn($query) => $query->price($price1, $price2))
            ->when($sizes, fn($query) => $query->size($sizes))
            ->when($colors, fn($query) => $query->color($colors))
            ->when($brands, fn($query) => $query->brand($brands))
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


    public function displaybyid($id, $user, ProductService $service)
    {
        $product = Product::with(['brands', 'shoesize', 'MainCategory:id,ka_name,en_name', 'Category:id,ka_name,en_name', 'Subcategory:id,ka_name,en_name', 'clothsize', 'spu.products' => function ($p) {
            $p->with(['shoesize', 'clothsize']);
        }])
            ->where('id', $id)
            ->first();
        $this->productHelper->getSizetype($product);
        $this->productHelper->getDiscountPrice($product, $user);


        $cartItems = $user ? $user->cart?->products->map(function ($product) {
            return [
                'product_id' => $product->pivot->product_id,
                'quantity' => $product->pivot->quantity
            ];
        }) : [];
        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];

        $varinats = $product->spu->products->map(function ($product) use ($service, $cartItems, $likedProductIds) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'isLiked' => in_array($product->id, $likedProductIds),
                'inCartQuantity' => collect($cartItems)->firstWhere('product_id', $product->id)['quantity'] ?? 0,
                'size' => $service->getSizeData($product),
                'image_urls' =>  $product->getMedia('default')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                })
            ];
        });
        return new SingleProductResource($product, $varinats);
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

    public function create(Request $request, SpuService $service)
    {
        $spuData = [
            'name' => $request->name,
            'brand' => Brand::firstWhere('id', $request->brand_id)->name,
            'category' => $request->size_type
        ];

        try {
            $spu_id = $service->createOrUpdate($spuData);
            $sku = $service->generateSku($request->name, $request->size[0], $request->color[0][0]);
            if (!$spu_id || !$sku) {
                return false;
            }

            $slug = Str::slug(
                $request->name . '-' .
                    (isset($request->size[0]) ? $request->size[0] : 'default_size') . '-' .
                    (isset($request->color[0][0]) ? $request->color[0][0] : 'default_color')
            );
            $product = Product::create([
                'name' => $request->name,
                'sku' => $sku,
                'description' => $request->description,
                'price' => $request->price,
                'discountprice' => $request->price,
                'maincategory_id' => $request->maincategory_id,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'spu_id' => $spu_id,
                'additionalinfo' => $request->additionalinfo,
                'slug' => $slug

            ]);

            $sizes = $request->input('size', []);
            $quantities = $request->input('quantity', []);
            $hex = $request->input('hex', []);
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
                            'hex' => $hex[$index][$colorindex],

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
                            'hex' => $hex[$index][$colorindex],
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
