<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductSize;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Clothsize;
use App\Models\Color;
use App\Models\Maincategory;
use App\Models\Product;
use App\Models\Shoessize;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function displaymain()
    {
        $maincategory = Maincategory::with('Categories')->select('id', 'ka_name', 'en_name')->get();

        $mappedmaincategory = $maincategory->map(function ($category) {
            return [
                "id" => $category->id,
                "ka_name" => $category->ka_name,
                "en_name" => $category->en_name,

                "categories" => $category->categories->map(function ($category) {
                    return [
                        "id" => $category->id,
                        "ka_name" => $category->ka_name,
                    ];
                })
            ];
        });

        return $mappedmaincategory;
    }


    public function Maincategory(Request $request)
    {
        Maincategory::create([
            "ka_name" => $request->ka_name,
            "en_name" => $request->en_name,
        ]);
        return response()->json([
            'message' => 'MainCategory Added',
        ]);
    }
    public function Maincategorydelete($id)
    {
        $maincategory = Maincategory::where('id', $id)->first();
        $maincategory->delete();
        return response()->json([
            'message' => 'MainCategory Deleted',

        ]);
    }
    public function Maincategorybyid($id)
    {
        $maincategory = Maincategory::select('id', 'name')->findorfail($id);
        return response()->json([
            "id" => $maincategory->id,
            "name" => $maincategory->name
        ]);
    }


    public function displaycategory()
    {
        $categories = Category::select('categories.id', 'categories.ka_name', 'categories.en_name')
            ->withCount('products')
            ->with([
                'maincategories' => fn($q) => $q->select('maincategories.id'),
                'subcategories'  => fn($q) => $q->select(
                    'subcategories.id',
                    'subcategories.ka_name',
                ),
            ])
            ->get()
            ->map(function ($cat) {
                $cat->setRelation('subcategories', $cat->subcategories->take(4));
                return $cat;
            });

        $mappedcategory = $categories->map(function ($category) {
            return [
                'id'              => $category->id,
                'ka_name'         => $category->ka_name,
                'en_name'         => $category->en_name,
                'maincategory_id' => $category->maincategories->pluck('id')->toArray(),
                "image_url" => $category->getMedia('category')->map(function ($media) {
                    return url('storage/' . $media->id . '/' . $media->file_name);
                }),
                'subcategories'   => $category->subcategories
                    ->map(fn($s) => [
                        'id'      => $s->id,
                        'ka_name' => $s->ka_name,
                    ])
                    ->values()
                    ->toArray(),

                'count'           => $category->products_count,
            ];
        })->values();

        return $mappedcategory;
    }

    public function displayadmincategory()
    {
        $categories = Category::with(['Subcategories' => function ($query) {
            $query->select('subcategories.id', 'subcategories.ka_name');
        }])->select('id', 'ka_name', 'en_name')->get();

        $mappedcategory = $categories->map(function ($category) {
            return [
                "id" => $category->id,
                "ka_name" => $category->ka_name,
                "en_name" => $category->en_name,
                "subcategories" => $category->subcategories->map(function ($subcategory) {
                    return [
                        "id" => $subcategory->id,
                        "ka_name" => $subcategory->ka_name,
                    ];
                })
            ];
        });

        return $mappedcategory;
    }



    public function assignMainRelation(Request $request)
    {
        $maincategory = Maincategory::findorfail($request->maincategory_id);
        $success = $maincategory->Categories()->attach($request->category_id);
        if ($success) {
            return response()->json(["message" => "added relation"]);
        }
    }
    public function deleteMainRelation(Request $request)
    {
        $maincategory = Maincategory::findorfail($request->maincategory_id);
        $success = $maincategory->Categories()->detach($request->category_id);
        if ($success) {
            return response()->json(["message" => "deleted relation"]);
        }
    }
    public function assignCategoryRelation(Request $request)
    {
        $category = Category::findorfail($request->category_id);
        $success = $category->Subcategories()->attach($request->subcategory_id);
        if ($success) {
            return response()->json(["message" => "added relation"]);
        }
    }
    public function deleteCategoryRelation(Request $request)
    {
        $category = Category::findorfail($request->category_id);
        $success = $category->Subcategories()->detach($request->subcategory_id);
        if ($success) {
            return response()->json(["message" => " relation"]);
        }
    }


    public function category(Request $request)
    {
        $validated = $request->validate([
            'ka_name' => 'required|string|max:255',
            'en_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $category = Category::create([
                'ka_name' => $validated['ka_name'],
                'en_name' => $validated['en_name'],
            ]);

            $category->addMediaFromRequest('image')->toMediaCollection('category');

            return response()->json([
                'message' => 'Category Added',
                'category' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function categorydelete($id)
    {
        $category = Category::where('id', $id)->first();
        $category->delete();
        return response()->json([
            'message' => 'Category deleted',

        ]);
    }

    public function categorybyid($id)
    {
        $category = Category::findorfail($id);
        return response()->json([
            "id" => $category->id,
            "name" => $category->name

        ]);
    }



    public function displaysub()
    {
        $subcategories = Subcategory::with(['Categories' => function ($q) {
            $q->select("categories.id", "categories.ka_name");
        }])->select("id", "ka_name", "en_name")->get();

        $mappedsubcategory = $subcategories->map(function ($subcategory) {
            return [
                "id" => $subcategory->id,
                "ka_name" => $subcategory->ka_name,
                "en_name" => $subcategory->en_name,
                "category_id" => $subcategory->categories->pluck("id")->toArray()
            ];
        });
        return $mappedsubcategory;
    }

    public function Subcategory(Request $request)
    {
        Subcategory::create([
            "ka_name" => $request->ka_name,
            "en_name" => $request->en_name,

        ]);
        return response()->json([
            'message' => 'SubCategory Added',
        ]);
    }
    public function Subcategorydelete($id)
    {
        $subcategory = Subcategory::where('id', $id)->first();
        $subcategory->delete();
        return response()->json([
            'message' => 'SubCategory deleted',

        ]);
    }
    public function subcategorybyid($id)
    {
        $subcategory = Subcategory::findorfail($id);
        return response()->json([
            "id" => $subcategory->id,
            "name" => $subcategory->name

        ]);
    }




    public function displaycolor()
    {
        $colors = Color::whereHasMorph(
            "colorable",
            [Shoessize::class, Clothsize::class]
        )->with(
            [
                'colorable' => function ($query) {
                    $query->select("id", "product_id");
                }
            ]
        )->select("id", "color", "colorable_id", "colorable_type")
            ->get()
            ->map(function ($color) {
                $product_id = $color->colorable->product_id;
                $product = Product::select("maincategory_id", "category_id", "subcategory_id")
                    ->findOrFail($product_id);


                return  [
                    "id" => $color->id,
                    "color" => $color->color,
                    "maincategory_id" => $product->maincategory_id,
                    "category_id" => $product->category_id,
                    "subcategory_id" => $product->subcategory_id,

                ];
            })->unique(function ($item) {
                return $item['color'];
            })->values();


        return response()->json($colors);
    }
    public function displaysize()
    {
        $clothsize = Clothsize::distinct()->orderByRaw("FIELD(size, 'xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl')")
            ->pluck("size");
        $shoessize = Shoessize::distinct()
            ->orderBy('size')
            ->pluck('size');

        return response()->json(['clothsize' => $clothsize, "shoessize" => $shoessize]);
    }

    public function allCategory()
    {
        $categories = Maincategory::select('id', 'ka_name')
            ->with([
                'Categories' => function ($query) {
                    $query->select('categories.id', 'categories.ka_name')
                        ->with([
                            'Subcategories' => function ($subQuery) {
                                $subQuery->select('subcategories.id', 'subcategories.ka_name');
                            }
                        ]);
                }
            ])
            ->get();
        return response()->json($categories);
    }
}
