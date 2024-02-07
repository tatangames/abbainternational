<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sistema\ApiSistemaController;
use App\Http\Controllers\Api\Registro\ApiRegistroController;
use App\Http\Controllers\Api\Perfil\ApiPerfilController;
use App\Http\Controllers\Api\Correo\ApiCorreoController;
use App\Http\Controllers\Api\Registro\ApiLoginController;
use App\Http\Controllers\Api\Planes\ApiPlanesController;
use App\Http\Controllers\Api\Inicio\ApiInicioController;
use App\Http\Controllers\Api\Comunidad\ApiComunidadController;


// inicio de sesion
Route::post('app/login', [ApiLoginController::class,'loginUsuario']);

// solicitar listado de iglesias segun id departamento
Route::post('app/solicitar/listado/iglesias', [ApiRegistroController::class,'listadoDeIglesias']);

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



    // *****************************************************************


    // buscar planes que no esten agregados a mi usuario -> Paginate
    Route::post('app/buscar/planes/nuevos', [ApiPlanesController::class,'buscarPlanesNoAgregados']);

    // ver informacion de un plan para poder seleccionarlo
    Route::post('app/plan/seleccionado/informacion', [ApiPlanesController::class,'informacionPlanSeleccionado']);

    // selecciona un plan para iniciarlo
    Route::post('app/plan/nuevo/seleccionar', [ApiPlanesController::class,'iniciarPlanNuevo']);


    // devuelve mis planes que he seleccionado, habra algunos que pasaran a 'completados' pero
    // se verificaran dinamicamente -> Paginate
    Route::post('app/plan/listado/misplanes', [ApiPlanesController::class,'listadoMisPlanes']);

    // devuelve informacion del plan a continuar, todos el bloque
    Route::post('app/plan/misplanes/informacion/bloque', [ApiPlanesController::class,'informacionBloqueMiPlan']);

    // actualizar el check de cada plan
    Route::post('app/plan/misplanes/actualizar/check', [ApiPlanesController::class,'actualizarCheckBloqueMiPlan']);

    // informacion de un cuestionario de un bloque detalle
    Route::post('app/plan/misplanes/cuestionario/bloque', [ApiPlanesController::class,'informacionCuestionarioBloque']);

    // informacion de preguntas de un bloque detalle
    Route::post('app/plan/misplanes/preguntas/bloque', [ApiPlanesController::class,'informacionPreguntasBloque']);

    // actualizar preguntas
    Route::post('app/plan/misplanes/preguntas/usuario/actualizar', [ApiPlanesController::class,'actualizarPreguntasUsuarioPlan']);

    // devuelve textos de preguntas y respuestas para compartir
    Route::post('app/plan/misplanes/preguntas/infocompartir', [ApiPlanesController::class,'informacionPreguntasParaCompartir']);















    // informacion de todos los planes completados
    Route::post('app/plan/misplanes/completados', [ApiPlanesController::class,'listadoMisPlanesCompletados']);

    // devuelve informacion del plan a continuar, todos el bloque pero esto solo es vista
    Route::post('app/plan/misplanes/info/bloque/vista', [ApiPlanesController::class,'informacionBloqueMiPlanVista']);





    //*** FRAGMENT INICIO



    // devuelve todos los elementos bloque inicio
    Route::post('app/inicio/bloque/completa', [ApiInicioController::class,'infoBloqueInicioCompleto']);

    // informacion de un plan, solo para vista, usando idblockdeta para buscar id plan
    Route::post('app/plan/informacion/solovista', [ApiInicioController::class,'infoPlanSoloVista']);

    // guardar preguntas del cuestionario, registrar usuario al plan, set check a true,
    Route::post('app/plan/inicio/preguntas/guardar/actualizar', [ApiInicioController::class,'preguntasInicioGuardarActualizar']);

    // obtener listado de todos los videos
    Route::post('app/inicio/todos/losvideos', [ApiInicioController::class,'listadoTodosLosVideos']);

    // obtener listado de todos las imagenes
    Route::post('app/inicio/todos/lasimagenes', [ApiInicioController::class,'listadoTodosLasImagenes']);

    // obtener listado de todas las insignias
    Route::post('app/inicio/todos/lasinsignias', [ApiInicioController::class,'listadoTodosLasInsignias']);

    // informacion de una insignia
    Route::post('app/insignia/individual/informacion', [ApiInicioController::class,'informacionInsigniaIndividual']);


    //*** COMUNIDAD

    // enviar solicitud a un amigo
    Route::post('app/comunidad/enviar/solicitud', [ApiComunidadController::class,'enviarSolicitudAmigo']);

    // listado de solicutes pendientes que yo he enviado
    Route::post('app/comunidad/listado/solicitud/pendientes', [ApiComunidadController::class,'listadoSolicitudesPendientes']);

    // listado de solicutes aceptadas que yo he enviado
    Route::post('app/comunidad/listado/solicitud/aceptadas', [ApiComunidadController::class,'listadoSolicitudesAceptadas']);

    // eliminar una solicitud
    Route::post('app/comunidad/solicitud/eliminar', [ApiComunidadController::class,'eliminarSolicitud']);

    // mostrar listado de insignias del usuario de comunidad
    Route::post('app/comunidad/informacion/insignias', [ApiComunidadController::class,'informacionInsigniaAmigo']);

    // listado de planes de ese usuario amigo para ver sus preguntas




});






