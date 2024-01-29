<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunidadSolicitud extends Model
{
    use HasFactory;
    protected $table = 'comunidad_solicitud';
    public $timestamps = false;
}
