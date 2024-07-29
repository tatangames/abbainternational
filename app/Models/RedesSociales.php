<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedesSociales extends Model
{
    use HasFactory;
    protected $table = 'redes_sociales';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
