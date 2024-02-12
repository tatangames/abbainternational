<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotificacionTextos extends Model
{
    use HasFactory;
    protected $table = 'tipo_notificacion_textos';
    public $timestamps = false;
}
