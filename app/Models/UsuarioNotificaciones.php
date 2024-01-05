<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioNotificaciones extends Model
{
    use HasFactory;
    protected $table = 'usuario_notificaciones';
    public $timestamps = false;
}
