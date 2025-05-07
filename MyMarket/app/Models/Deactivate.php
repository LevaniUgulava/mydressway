<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deactivate extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'token', 'expires_at', 'used'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
