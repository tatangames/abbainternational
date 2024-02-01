<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenesDelDia extends Model
{
    use HasFactory;
    protected $table = 'imagenes_dia';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
