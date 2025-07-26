<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = ['town', 'address', 'additionalInfo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function OrderStatus($status)
    {
        return $this->orders->contains(function ($o) use ($status) {
            return $o->status === $status;
        });
    }
}
