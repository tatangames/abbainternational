<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVideo extends Model
{
    use HasFactory;
    protected $table = 'tipo_video';
    public $timestamps = false;
}
