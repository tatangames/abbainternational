<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosTextos extends Model
{
    use HasFactory;
    protected $table = 'videos_textos';
    public $timestamps = false;
}
