<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sistema\ApiSistemaController;
use App\Http\Controllers\Api\Registro\ApiRegistroController;
use App\Http\Controllers\Api\Perfil\ApiPerfilController;
use App\Http\Controllers\Api\Correo\ApiCorreoController;
use App\Http\Controllers\Api\Registro\ApiLoginController;



// inicio de sesion
Route::post('app/login', [ApiLoginController::class,'loginUsuario']);

// registro de usuario
Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);

// solicitar codigo segun correo
Route::post('app/solicitar/codigo/contrasena', [ApiCorreoController::class,'enviarCorreoRecuperacion']);

// verificar codigo y retornar token
Route::post('app/verificar/codigo/recuperacion', [ApiCorreoController::class,'verificarCodigoRecuperacion']);



Route::middleware('verificarToken')->group(function () {

    // actualizar perfil utilizando token
    Route::post('app/actualizar/nueva/contrasena/reseteo', [ApiCorreoController::class,'actualizarNuevaPasswordReseteo']);


    // cuabre se abre fragment ajustes
    Route::post('app/solicitar/listado/opcion/perfil', [ApiPerfilController::class,'informacionAjustes']);

    // informacion de mi perfil
    Route::post('app/solicitar/informacion/perfil', [ApiPerfilController::class,'informacionPerfilUsuario']);

    // actualizar datos de mi perfil
    Route::post('app/actualizar/perfil/usuario', [ApiPerfilController::class,'actualizarPerfilUsuario']);




});










