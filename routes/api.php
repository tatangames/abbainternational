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



Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);


Route::post('app/solicitar/listado/opcion/perfil', [ApiPerfilController::class,'informacionAjustes']);

Route::post('app/actualizar/perfil/usuario', [ApiPerfilController::class,'actualizarPerfilUsuario']);

Route::post('app/solicitar/codigo/contrasena', [ApiCorreoController::class,'enviarCorreoRecuperacion']);

Route::post('app/verificar/codigo/recuperacion', [ApiCorreoController::class,'verificarCodigoRecuperacion']);

Route::post('app/actualizar/nueva/contrasena/reseteo', [ApiCorreoController::class,'actualizarNuevaPasswordReseteo']);






Route::middleware('verificarToken')->group(function () {
    // Rutas protegidas por el middleware de verificar token

    Route::post('app/solicitar/informacion/perfil', [ApiPerfilController::class,'informacionPerfilUsuario']);


});










