<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = [];
    protected $table = 'cart';
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_item')
            ->withPivot(['id', 'quantity', 'size', 'color', 'retail_price', 'total_price', 'isOriginal']);
    }
}
