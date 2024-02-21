<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibliaVersiculo extends Model
{
    use HasFactory;
    protected $table = 'biblia_versiculo';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
