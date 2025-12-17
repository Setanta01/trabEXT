<?php
// app/Models/CustomGameMode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomGameMode extends Model
{
    protected $fillable = [
        'title',
        'description',
        'creator_name',
    ];

    public function questions()
    {
        return $this->hasMany(CustomQuestion::class, 'game_mode_id');
    }
}