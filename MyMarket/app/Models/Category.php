<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

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
}
