<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanesAmigos extends Model
{
    use HasFactory;
    protected $table = 'planes_amigos';
    public $timestamps = false;
}
