<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refreshtoken extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'client_type',
        'token_hash',
        'expires_at',
        'revoked_at'
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
