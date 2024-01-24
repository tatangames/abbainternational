<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturaDia extends Model
{
    use HasFactory;
    protected $table = 'lectura_dia';
    public $timestamps = false;
}
