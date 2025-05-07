<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function Categories()
    {
        return $this->belongsToMany(Category::class, "category_subcategory");
    }
    public function Products()
    {
        return $this->hasMany(Product::class);
    }

    public function Maincategory()
    {
        return $this->belongsTo(Maincategory::class);
    }
}
