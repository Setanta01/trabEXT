<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomQuestion extends Model
{
    protected $fillable = [
        'game_mode_id',
        'question',
        'correct_answer',
        'wrong_answer_1',
        'wrong_answer_2',
        'wrong_answer_3',
        'city_name',
        'city_lat',
        'city_lng',
    ];

    public function gameMode()
    {
        return $this->belongsTo(CustomGameMode::class, 'game_mode_id');
    }
}