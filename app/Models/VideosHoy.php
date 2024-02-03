<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosHoy extends Model
{
    use HasFactory;
    protected $table = 'videos_hoy';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
