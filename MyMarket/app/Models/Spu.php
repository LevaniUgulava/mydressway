<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'brand', 'category', 'description'];


    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
