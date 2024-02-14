<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionUsuario extends Model
{
    use HasFactory;
    protected $table = 'notificacion_usuario';
    public $timestamps = false;
}
