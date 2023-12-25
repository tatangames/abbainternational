<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sistema\ApiSistemaController;
use App\Http\Controllers\Api\Registro\ApiRegistroController;



Route::get('app/refran/login', [ApiSistemaController::class,'refranLogin']);

Route::post('app/login', [ApiRegistroController::class,'loginUsuario']);

Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);









