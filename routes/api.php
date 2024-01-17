<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sistema\ApiSistemaController;
use App\Http\Controllers\Api\Registro\ApiRegistroController;
use App\Http\Controllers\Api\Perfil\ApiPerfilController;
use App\Http\Controllers\Api\Correo\ApiCorreoController;
use App\Http\Controllers\Api\Registro\ApiLoginController;
use App\Http\Controllers\Api\Planes\ApiPlanesController;



// inicio de sesion
Route::post('app/login', [ApiLoginController::class,'loginUsuario']);

// registro de usuario
Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);

// solicitar codigo segun correo
Route::post('app/solicitar/codigo/contrasena', [ApiCorreoController::class,'enviarCorreoRecuperacion']);

// verificar codigo y retornar token
Route::post('app/verificar/codigo/recuperacion', [ApiCorreoController::class,'verificarCodigoRecuperacion']);


Route::post('app/solicitar/listado/opcion/perfil', [ApiPerfilController::class,'informacionAjustes']);


Route::middleware('verificarToken')->group(function () {

    // actualizar nueva contrasena en vista login
    Route::post('app/actualizar/nueva/contrasena/reseteo', [ApiCorreoController::class,'actualizarNuevaPasswordReseteo']);

    // cuando se abre fragment ajustes

    // informacion de mi perfil
    Route::post('app/solicitar/informacion/perfil', [ApiPerfilController::class,'informacionPerfilUsuario']);

    // actualizar datos de mi perfil
    Route::post('app/actualizar/perfil/usuario', [ApiPerfilController::class,'actualizarPerfilUsuario']);

    // actualizar contrasena cuando ya inicio sesion
    Route::post('app/actualizar/contrasena', [ApiPerfilController::class,'actualizarPassword']);

    // buscar planes que no esten agregados a mi usuario
    Route::post('app/buscar/planes/nuevos', [ApiPlanesController::class,'buscarPlanesNoAgregados']);

    // ver informacion de un plan para poder seleccionarlo
    Route::post('app/plan/seleccionado/informacion', [ApiPlanesController::class,'informacionPlanSeleccionado']);

    // selecciona un plan para iniciarlo
    Route::post('app/plan/nuevo/seleccionar', [ApiPlanesController::class,'iniciarPlanNuevo']);


    // devuelve lista de planes que no he seleccionado aun, por id contenedor
    Route::post('app/plan/listado/planes/contenedor', [ApiPlanesController::class,'listadoPlanesContenedor']);

    // devuelve mis planes que he seleccionado, habra algunos que pasaran a 'completados' pero
    // se verificaran dinamicamente
    Route::post('app/plan/listado/misplanes', [ApiPlanesController::class,'listadoMisPlanes']);

    // devuelve informacion del plan a continuar, todos el bloque
    Route::post('app/plan/misplanes/informacion/bloque', [ApiPlanesController::class,'informacionBloqueMiPlan']);






});










