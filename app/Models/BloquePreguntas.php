<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloquePreguntas extends Model
{
    use HasFactory;
    protected $table = 'bloque_preguntas';
    public $timestamps = false;
}
