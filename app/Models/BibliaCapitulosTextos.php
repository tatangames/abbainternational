<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibliaCapitulosTextos extends Model
{
    use HasFactory;
    protected $table = 'biblia_capitulos_textos';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
