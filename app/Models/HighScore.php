<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HighScore extends Model
{
    protected $fillable = ['player_name', 'score'];
}