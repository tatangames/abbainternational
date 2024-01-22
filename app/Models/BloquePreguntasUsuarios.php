<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloquePreguntasUsuarios extends Model
{
    use HasFactory;
    protected $table = 'bloque_preguntas_usuarios';
    public $timestamps = false;

}
