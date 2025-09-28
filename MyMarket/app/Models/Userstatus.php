<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userstatus extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function userstatusinfo()
    {
        return $this->hasOne(Userstatusinfo::class);
    }

    public function eligibleProducts()
    {
        return $this->belongsToMany(Product::class, 'eligibleproducts', 'userstatus_id', 'product_id');
    }
    public function isActive($user): bool
    {
        if (!$user || !optional($user->userstatusinfo)->end_time) {
            return false;
        }

        $acquiredAt = optional($user->userstatusinfo)->end_time;

        if (!$acquiredAt instanceof Carbon) {
            $acquiredAt = Carbon::parse($acquiredAt);
        }

        return now()->lt($acquiredAt);
    }
}
