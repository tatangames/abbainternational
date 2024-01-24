<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibliaCapitulosTextos extends Model
{
    use HasFactory;
    protected $table = 'biblias_capitulos_textos';
    public $timestamps = false;
}
