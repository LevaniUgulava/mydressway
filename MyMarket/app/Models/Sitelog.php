<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Sitelog extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAction($query, $action)
    {
        if ($action) {
            return $query->where("action", $action);
        }
    }

    public function scopeUser($query, $user)
    {
        if ($user) {
            return $query->where('user_email', $user);
        }
    }
}
