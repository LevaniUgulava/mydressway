<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function isActive($user): bool
    {
        if (!$user || !$user->userstatus_time) {
            return false;
        }

        $acquiredAt = $user->userstatus_time instanceof Carbon
            ? $user->userstatus_time
            : Carbon::parse($user->userstatus_time);

        switch (strtolower($this->expansion)) {
            case 'day':
                $expiresAt = $acquiredAt->copy()->addDays($this->time);
                break;
            case 'month':
                $expiresAt = $acquiredAt->copy()->addMonthsNoOverflow($this->time);
                break;
            case 'year':
                $expiresAt = $acquiredAt->copy()->addYears($this->time);
                break;
            default:
                return false;
        }

        return now()->lt($expiresAt);
    }
}
