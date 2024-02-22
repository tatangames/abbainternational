<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Versiculo extends Model
{

    use HasFactory;
    protected $table = 'versiculo';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
