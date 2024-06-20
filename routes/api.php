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
use App\Http\Controllers\Api\Biblia\ApiBibliaController;
use App\Http\Controllers\Api\Biblia\ApiDevocionalBibliaController;


// inicio de sesion
Route::post('app/login', [ApiLoginController::class,'loginUsuario']);

// solicitar listado de iglesias segun id departamento
Route::post('app/solicitar/listado/iglesias', [ApiRegistroController::class,'listadoDeIglesias']);

// registro de usuario
Route::post('app/registro/usuario', [ApiRegistroController::class,'registroUsuario']);

// Fix 01/05/2024
// registro de usuario
Route::post('app/registro/usuario/v2', [ApiRegistroController::class,'registroUsuarioV2']);



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



    // MIS PLANES UTILIZADO PARA ANDROID Y IPHONE, YA NO USAR PAGINACION
    Route::post('app/plan/listado/misplanes', [ApiPlanesController::class,'listadoMisPlanesNoPaginacion']);
    Route::post('app/buscar/planes/nuevos', [ApiPlanesController::class,'buscarPlanesNoAgregadosNoPaginacion']);














    // devuelve informacion del plan BLOQUE FECHAS
    Route::post('app/plan/misplanes/informacion/bloque', [ApiPlanesController::class,'informacionBloqueMiPlan']);



    // informacion de un cuestionario de un bloque detalle
    Route::post('app/plan/misplanes/cuestionario/bloque', [ApiPlanesController::class,'informacionCuestionarioBloque']);

    // informacion de preguntas de un bloque detalle
    Route::post('app/plan/misplanes/preguntas/bloque', [ApiPlanesController::class,'informacionPreguntasBloque']);



    // CREAR / ACTUALIZAR PREGUNTAS PARA ANDROID
    Route::post('app/plan/misplanes/preguntas/usuario/actualizar', [ApiPlanesController::class,'actualizarPreguntasUsuarioPlan']);

    // IPHONE
    Route::post('app/plan/misplanes/preguntas/usuario/actualizariphone', [ApiPlanesController::class,'actualizarPreguntasUsuarioPlanIphone']);




    // SOLO LO USA IPHONE 18/05/2024
    // informacion de todos los planes completados -> Paginate
    Route::post('app/plan/misplanes/completados', [ApiPlanesController::class,'listadoMisPlanesCompletados']);








    //*** FRAGMENT INICIO

    // devuelve todos los elementos bloque inicio
    Route::post('app/inicio/bloque/completa', [ApiInicioController::class,'infoBloqueInicioCompleto']);

    // obtener listado de todos los videos
    Route::post('app/inicio/todos/losvideos', [ApiInicioController::class,'listadoTodosLosVideos']);


    // obtener listado de todos las imagenes
    Route::post('app/inicio/todos/lasimagenes', [ApiInicioController::class,'listadoTodosLasImagenes']);


    // informacion de una insignia
    Route::post('app/insignia/individual/informacion', [ApiInicioController::class,'informacionInsigniaIndividual']);

    // obtener listado de niveles de una insignia
    Route::post('app/insignia/niveles/informacion', [ApiInicioController::class,'listadoNivelesDeInsignia']);





    // obtener listado de todas las insignias
    Route::post('app/inicio/todos/lasinsignias', [ApiInicioController::class,'listadoTodosLasInsignias']);


    // listado de todas las insignias faltantes
    Route::post('app/listado/insignias/faltantes', [ApiInicioController::class,'listadoInsigniasFaltantesPorGanar']);








    // guardar las veces que comparte aplicacion -> onesignal
    Route::post('app/compartir/aplicacion', [ApiInicioController::class,'compartirAplicacion']);

    // compartir devocional
    Route::post('app/compartir/devocional', [ApiInicioController::class,'compartirDevocional']);

    // devuelve textos de preguntas y respuestas para compartir -> One Signal insignia
    // utilizado en MisPlanesBloquesFechaActivity
    Route::post('app/plan/misplanes/preguntas/infocompartir', [ApiPlanesController::class,'informacionPreguntasParaCompartir']);


    // actualizar el check de cada plan -> onesignal
    // INSIGNIA RACHA DIA LECTURA
    // INSIGNIA COMPLETAR PLAN
    // INSIGNIA PLANES COMPARTIDOS EN GRUPOS
    Route::post('app/plan/misplanes/actualizar/check', [ApiPlanesController::class,'actualizarCheckBloqueMiPlan']);


    // Listado notificaciones para el usuario -> Paginate
    Route::post('app/notificaciones/listado', [ApiComunidadController::class,'listadoNotificaciones']);










    //*** COMUNIDAD

    // enviar solicitud a un amigo
    Route::post('app/comunidad/enviar/solicitud', [ApiComunidadController::class,'enviarSolicitudAmigo']);

    // listado de solicitudes pendientes que yo he enviado
    Route::post('app/comunidad/listado/solicitud/pendientes/enviadas', [ApiComunidadController::class,'listadoSolicitudesPendientesEnviadas']);

    // listado de solicitudes pendientes que yo he recibido
    Route::post('app/comunidad/listado/solicitud/pendientes/recibidas', [ApiComunidadController::class,'listadoSolicitudesPendientesRecibidas']);

    // listado de solicitudes aceptadas
    Route::post('app/comunidad/listado/solicitud/aceptadas', [ApiComunidadController::class,'listadoSolicitudesAceptadas']);

    // eliminar una solicitud
    Route::post('app/comunidad/solicitud/eliminar', [ApiComunidadController::class,'eliminarSolicitud']);

    // aceptar una solicitud que he recibido
    Route::post('app/comunidad/aceptarsolicitud/recibido', [ApiComunidadController::class,'aceptarSolicitudRecibido']);

    // informacion insignia amigo
    Route::post('app/comunidad/informacion/insignias', [ApiComunidadController::class,'informacionInsigniaAmigo']);



    // iniciar plan con amigos
    Route::post('app/comunidadplan/iniciar/plan/amigos', [ApiComunidadController::class,'iniciarPlanConAmigos']);

    // iniciar plan con amigos IPHONE
    Route::post('app/comunidadplan/iniciar/plan/amigosiphone', [ApiComunidadController::class,'iniciarPlanConAmigosIphone']);




    // listado de planes que tiene ese amigo de comunidad
    Route::post('app/comunidad/informacion/planes', [ApiComunidadController::class,'informacionPlanesAmigo']);

    // listado de items de ese plan para ver preguntas
    Route::post('app/comunidad/informacion/planes/items', [ApiComunidadController::class,'informacionPlanesAmigoItems']);

    // informacion preguntas para usuario comunidad
    Route::post('app/comunidad/informacion/planes/itemspreguntas', [ApiComunidadController::class,'informacionPlanesAmigoItemsPreguntas']);






    // AMIGOS QUE YO HE AGREGADO EN PLAN COMUNIDAD, muestra los planes listado
    Route::post('app/comunidad/yoagregue/planeslistado', [ApiComunidadController::class,'listadoPlanesYoAgregueComunidad']);

    // LISTADO DE AMIGOS, MAX 5, QUE SE MUESTRAN Y VEO COMO VAN
    Route::post('app/comunidad/yoagregue/planeslistado/informacion', [ApiComunidadController::class,'listadoPlanesYoAgregueComunidaInformacion']);

    // LISTADO DE AMIGOS QUE ME AGREGARON A UN PLAN
    Route::post('app/comunidad/mehanagregado/planes/amigos', [ApiComunidadController::class,'listadoAmigosMeHanAgregadoPlan']);

    // HOY QUE YA SE EL USUARIO ME AGREGO UN PLAN, QUIERO SABER EL LISTADO DE PLANES QUE ME AGREGO
    Route::post('app/comunidad/mehanagregado/planes/listado', [ApiComunidadController::class,'listadoAmigosMeHanAgregadoPlanListado']);













    // --- BIBLIAS ----
    // listado de capitulos, su titulo y todos los bloques
    Route::post('app/listado/biblias', [ApiBibliaController::class,'listadoBiblias']);

    // listado de capitulos para usar el Acordeon en Apps
    Route::post('app/listado/biblia/capitulos', [ApiBibliaController::class,'listadoBibliasCapitulos']);



    // todos los textos del versiculo
    Route::post('app/listado/bibliacapitulo/textos', [ApiBibliaController::class,'listadoTextosCapituloBiblia']);



    // --- LECTURA BIBLIA EN DEVOCIONALES ---
    Route::post('app/listado/biblia/devocionales', [ApiDevocionalBibliaController::class,'listadoCapituloVersiculo']);

















});

