<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RachasUsuario extends Model
{
    use HasFactory;
    protected $table = 'rachas_usuario';
    public $timestamps = false;
}
