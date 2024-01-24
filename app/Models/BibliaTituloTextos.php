<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibliaTituloTextos extends Model
{
    use HasFactory;
    protected $table = 'biblia_titulo_textos';
    public $timestamps = false;
}
