<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sistema\ApiSistemaController;
use App\Http\Controllers\Api\Registro\ApiRegistroController;
use App\Http\Controllers\Api\Perfil\ApiPerfilController;
use App\Http\Controllers\Api\Correo\ApiCorreoController;



Route::get('app/refran/login', [ApiSistemaController::class,'refranLogin']);

Route::post('app/login', [ApiRegistroController::class,'loginUsuario']);

Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);

Route::post('app/solicitar/informacion/perfil', [ApiPerfilController::class,'informacionPerfilUsuario']);

Route::post('app/solicitar/listado/opcion/perfil', [ApiPerfilController::class,'informacionAjustes']);

Route::post('app/actualizar/perfil/usuario', [ApiPerfilController::class,'actualizarPerfilUsuario']);

Route::post('app/solicitar/codigo/contrasena', [ApiCorreoController::class,'enviarCorreoRecuperacion']);

Route::post('app/verificar/codigo/recuperacion', [ApiCorreoController::class,'verificarCodigoRecuperacion']);




















