<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryOrder extends Model
{
    use HasFactory;
    protected $table = "temporary_orders";

    protected $fillable = [
        "user_id",
        "guest_token",
        "name",
        "color",
        "type",
        "quantity",
        "product_id",
        "size",
        "retail_price",
        "total_price"
    ];
}
