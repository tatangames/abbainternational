<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanesContenedor extends Model
{
    use HasFactory;
    protected $table = 'planes_contenedor';
    public $timestamps = false;
}
