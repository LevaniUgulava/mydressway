<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'google_id',
        'confirmation_token',
        'email_verified_at',
        'userstatus_id',
        'userstatus_time',
        'privacy_policy_agreed',
        'marketing_opt_in'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'userstatus_time' => 'datetime',
    ];



    public function manyproducts()
    {
        return $this->belongsToMany(Product::class, 'user_product');
    }


    public function commentproduct()
    {
        return $this->belongsToMany(Product::class, 'product_comment')->withPivot('comment')->withTimestamps();
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function Sitelogs()
    {
        return $this->hasMany(Sitelog::class)->wherehas('user', function ($query) {
            $query->wherehas('roles', function ($q) {
                $q->where('name', '!=', 'default');
            });
        });
    }

    public function rateuser()
    {
        return $this->hasMany(RateProduct::class, 'user_id');
    }

    public function userstatusinfo()
    {
        return $this->hasOne(Userstatusinfo::class);
    }


    public function getPriceByStatus($product, $price, $defaultPrice)
    {
        $this->loadMissing('userstatusinfo.userstatus');

        $status = optional($this->userstatusinfo)->userstatus;

        if ($status) {
            $isEligible = $product->eligibleStatuses()
                ->where('userstatus_id', $status->id)
                ->withPivot('discount')
                ->first();

            if ($isEligible && $isEligible->pivot->discount > 0 && $status->isActive($this)) {
                $discount = $isEligible->pivot->discount;
                return round($price * (1 - ($discount / 100)), 2);
            }
        }

        return round($defaultPrice, 2);
    }


    public function searchies()
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function temporders()
    {
        return $this->hasMany(TemporaryOrder::class);
    }

    public function usertemp()
    {
        return $this->hasOne(Usertemp::class);
    }
}
