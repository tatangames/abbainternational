<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionTextos extends Model
{
    use HasFactory;
    protected $table = 'notificacion_textos';
    public $timestamps = false;
}
