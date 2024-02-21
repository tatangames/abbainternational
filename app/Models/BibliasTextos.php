<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibliasTextos extends Model
{
    use HasFactory;
    protected $table = 'biblias_textos';
    public $timestamps = false;

    protected $fillable = [
        'posicion'
    ];
}
