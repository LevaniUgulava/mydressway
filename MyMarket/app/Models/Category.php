<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    public function Subcategories()
    {
        return $this->belongsToMany(Subcategory::class, "category_subcategory");
    }
    public function Products()
    {
        return $this->hasMany(Product::class);
    }

    public function Maincategories()
    {
        return $this->belongsToMany(Maincategory::class, "maincategory_category");
    }
    public function promocodes()
    {
        return $this->belongsToMany(Promocode::class, 'category_promocode');
    }
}
