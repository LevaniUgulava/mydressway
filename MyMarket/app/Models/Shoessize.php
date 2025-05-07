<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoessize extends Model
{
    use HasFactory;

    protected $fillable = ['size', 'product_id'];


    public function products()
    {
        return $this->belongsTo(Product::class);
    }

    public function quantities()
    {
        return $this->morphMany(Quantity::class, 'quantifiable');
    }

    public function colors()
    {
        return $this->morphMany(Color::class, 'colorable');
    }
}
