<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;
    protected $table = "search_histories";

    protected $fillable = ['user_id', 'term'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
