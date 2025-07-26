<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

// #[ObservedBy([ProductLoggerObserver::class])]

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded = [];
    protected $casts = [
        'size' => 'array',
    ];




    public function scopeSearchname($query, $name)
    {
        if ($name) {
            return $query->where('name', 'LIKE', '%' . $name . '%');
        }

        return $query;
    }

    public function scopeSearchmain($query, $maincategoryids)
    {
        if ($maincategoryids) {
            $maincategoryid = array_map('intval', explode(",", $maincategoryids));
            return $query->whereIn('maincategory_id', $maincategoryid);
        }
    }


    public function scopeSearchcategory($query, $categoryids)
    {
        if ($categoryids) {
            $catids = array_map("intval", explode(",", $categoryids));
            return $query->whereIn('category_id', $catids);
        }
    }


    public function scopeSearchsubcategory($query, $subcategoryids)
    {
        if ($subcategoryids) {
            $subids = array_map("intval", explode(",", $subcategoryids));
            return $query->whereIn('subcategory_id', $subids);
        }
    }



    public function scopeSection($query, $user, $section)
    {
        if ($section === "all") {
            $query;
        } elseif ($section === "discount") {
            $query->where(function ($q) use ($user) {
                $q->where('discount', '!=', 0);
                if ($user && isset($user->userstatus) && $user->userstatus->isActive()) {
                    $q->orWhereHas('eligibleStatuses', function ($q2) use ($user) {
                        $q2->where('userstatus_id', $user->userstatus->id)
                            ->where('discount', '!=', 0);
                    });
                }
            });
        }
        return $query;
    }
    public function scopePrice($query, $price1, $price2)
    {
        if ($price1 && $price2) {
            $query->whereBetween('discountprice', [$price1, $price2]);
        } elseif ($price1) {
            $max = Cache::remember('product_max_discountprice', 60, function () {
                return Product::max('discountprice');
            });
            $query->whereBetween('discountprice', [$price1, $max]);
        } elseif ($price2) {
            $min = Cache::remember('product_min_discountprice', 60, function () {
                return Product::min('discountprice');
            });
            $query->whereBetween('discountprice', [$min, $price2]);
        }
        return $query;
    }
    function scopeSize($query, $sizes)
    {
        $sizesmap = explode(",", $sizes);
        return $query->where(function ($query) use ($sizesmap) {
            $query->whereHas('shoesize', function ($query) use ($sizesmap) {
                $query->whereIn("size", $sizesmap);
            })
                ->orWhereHas('clothsize', function ($query) use ($sizesmap) {
                    $query->whereIn("size", $sizesmap);
                });
        });
    }

    public function scopeColor($query, $colors)
    {
        $colorsmap = explode(",", $colors);
        return $query->where(function ($query) use ($colorsmap) {
            $query->whereHas('shoesize', function ($query) use ($colorsmap) {
                $query->whereHas('colors', function ($query) use ($colorsmap) {
                    $query->where('color', $colorsmap);
                });
            })
                ->orWhereHas('clothsize', function ($query) use ($colorsmap) {
                    $query->whereHas('colors', function ($query) use ($colorsmap) {
                        $query->where('color', $colorsmap);
                    });
                });
        });
    }
    public function scopeBrand($query, $brands)
    {
        $brandsmap = explode(",", $brands);
        return $query->whereHas("brands", function ($query) use ($brandsmap) {
            $query->whereIn('brand_id', $brandsmap);
        });
    }



    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function Subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function Maincategory()
    {
        return $this->belongsTo(Maincategory::class);
    }



    public static function boot()
    {
        parent::boot();
        static::saving(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_product');
    }

    public function commentusers()
    {
        return $this->belongsToMany(User::class, 'product_comment')->withPivot('comment')->withTimestamps();
    }


    public function shoesize()
    {
        return $this->hasMany(Shoessize::class);
    }

    public function clothsize()
    {
        return $this->hasMany(Clothsize::class);
    }


    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_item')
            ->withPivot(['quantity', 'size', 'color', 'retail_price', 'total_price']);
    }


    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_item')
            ->withPivot('quantity', 'size', 'retail_price', 'total_price')
            ->withTimestamps();
    }

    public function rateproduct()
    {
        return $this->hasMany(RateProduct::class, 'product_id');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product')->withTimestamps();
    }
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brand');
    }

    public function eligibleStatuses()
    {
        return $this->belongsToMany(Userstatus::class, 'eligibleproducts', 'product_id', 'userstatus_id')
            ->withPivot('userstatus_id');
    }
}
