<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userstatus extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function Users()
    {
        return $this->hasMany(User::class);
    }

    public function eligibleProducts()
    {
        return $this->belongsToMany(Product::class, 'eligibleproducts', 'userstatus_id', 'product_id');
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->start_data <= $now && ($this->end_date === null || $this->end_date >= $now);
    }
}
