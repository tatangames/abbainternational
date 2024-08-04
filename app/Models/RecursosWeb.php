<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursosWeb extends Model
{
    use HasFactory;
    protected $table = 'recursos';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
