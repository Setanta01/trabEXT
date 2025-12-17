<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'player_name',
        'score',
        'rounds_completed',
        'played_at'
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];
}