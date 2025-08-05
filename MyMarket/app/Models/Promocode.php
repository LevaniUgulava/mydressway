<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;
    public $guarded = [];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_promocode');
    }
}
