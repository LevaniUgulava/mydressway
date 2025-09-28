<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userstatusinfo extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'userstatus_id', 'userstatus_time', 'left', 'end_time'];


    protected $casts = [
        'userstatus_time' => 'datetime',
        'end_time'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userstatus()
    {
        return $this->belongsTo(Userstatus::class);
    }
}
