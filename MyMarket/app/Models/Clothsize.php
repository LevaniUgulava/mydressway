<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clothsize extends Model
{
    use HasFactory;
    protected $fillable = ['size', 'product_id'];

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($clothsize) {
            $clothsize->quantities()->delete();

            $product = Product::where('id', $clothsize->product_id)->first();
            if (!$product->clothsize()->exists()) {
                $product->update([
                    "active" => 0
                ]);
            }
        });
    }

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
