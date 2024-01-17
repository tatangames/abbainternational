<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaHoraria extends Model
{
    use HasFactory;
    protected $table = 'zona_horaria';
    public $timestamps = false;
}
