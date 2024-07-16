<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Backend\Dashboard\DashboardController;
use App\Http\Controllers\Controles\ControlRolController;
use App\Http\Controllers\Backend\Roles\PermisoController;
use App\Http\Controllers\Backend\Roles\RolesController;
use App\Http\Controllers\Backend\Sistema\PerfilController;
use App\Http\Controllers\Backend\Regiones\RegionesController;
use App\Http\Controllers\Backend\Usuarios\UsuariosController;
use App\Http\Controllers\Backend\Recursos\RecursosController;
use App\Http\Controllers\Backend\Planes\PlanesController;
use App\Http\Controllers\Backend\Planes\PreguntasController;
use App\Http\Controllers\Backend\Recursos\DevoInicioController;
use App\Http\Controllers\Backend\Recursos\InsigniasController;
use App\Http\Controllers\Backend\Biblias\BibliasController;
use App\Http\Controllers\Backend\Biblias\BibliaCapituloController;
use App\Http\Controllers\Backend\Devocional\DevoBibliaController;


Route::get('/', [LoginController::class,'index'])->name('login');

Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- ROLES ---
Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

// --- PERMISOS ---
Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);


// --- VISTA PARA INGRESAR CORREO ---
Route::get('/admin/ingreso/de/correo', [LoginController::class,'indexIngresoDeCorreo']);
Route::post('/admin/enviar/correo/password', [LoginController::class, 'enviarCorreoAdministrador']);


// VISTA AQUI SE INGRESA LA NUEVA CONTRASEÑA PORQUE EL LINK ES VALIDO
Route::get('/admin/resetear/contrasena/administrador/{token}', [LoginController::class,'indexIngresoNuevaPasswordLink']);

// VISTA SIN TOKEN PARA REDIRECCION
Route::get('/admin/resetear/contrasena/administrador', [LoginController::class,'indexIngresoNuevaPasswordLinkRedireccion']);


// ACTUALIZACION DE CONTRASENA

Route::post('/admin/administrador/actualizacion/password', [LoginController::class, 'actualizarPasswordAdministrador']);


// --- SIN PERMISOS VISTA 403 ---
Route::get('sin-permisos', [ControlRolController::class,'indexSinPermiso'])->name('no.permisos.index');



// --- CONTROL WEB ---
Route::get('/panel', [ControlRolController::class,'indexRedireccionamiento'])->name('admin.panel');


// --- PERFIL ---
Route::get('/admin/perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
Route::post('/admin/perfil/actualizar/todo', [PerfilController::class, 'editarUsuario']);




// --- DASHBOARD ---

Route::get('/admin/dashboard/index', [DashboardController::class,'index'])->name('admin.dashboard.index');


// --- REGIONES ---

// Pais
Route::get('/admin/region/pais/index', [RegionesController::class,'indexPais'])->name('admin.region.pais.index');
Route::get('/admin/region/pais/tabla', [RegionesController::class,'tablaPais']);
Route::post('/admin/region/pais/nuevo', [RegionesController::class,'nuevoPais']);
Route::post('/admin/region/pais/informacion', [RegionesController::class,'informacionPais']);
Route::post('/admin/region/pais/actualizar', [RegionesController::class,'actualizarPais']);

// Departamentos
Route::get('/admin/region/departamento/index/{id}', [RegionesController::class,'indexDepartamentos']);
Route::get('/admin/region/departamento/tabla/{id}', [RegionesController::class,'tablaDepartamentos']);
Route::post('/admin/region/departamento/nuevo', [RegionesController::class,'nuevoDepartamento']);
Route::post('/admin/region/departamento/informacion', [RegionesController::class,'informacionDepartamento']);
Route::post('/admin/region/departamento/actualizar', [RegionesController::class,'actualizarDepartamento']);

// Zona horarias -> id pais
Route::get('/admin/region/zonahoraria/index/{id}', [RegionesController::class,'indexZonaHoraria']);
Route::get('/admin/region/zonahoraria/tabla/{id}', [RegionesController::class,'tablaZonaHoraria']);
Route::post('/admin/region/zonahoraria/nuevo', [RegionesController::class,'nuevoZonaHoraria']);
Route::post('/admin/region/zonahoraria/informacion', [RegionesController::class,'informacionZonaHoraria']);
Route::post('/admin/region/zonahoraria/actualizar', [RegionesController::class,'actualizarZonaHoraria']);

// Iglesias -> id departamento
Route::get('/admin/region/iglesias/index/{id}', [RegionesController::class,'indexIglesia']);
Route::get('/admin/region/iglesias/tabla/{id}', [RegionesController::class,'tablaIglesia']);
Route::post('/admin/region/iglesias/nuevo', [RegionesController::class,'nuevaIglesia']);
Route::post('/admin/region/iglesias/informacion', [RegionesController::class,'informacionIglesia']);
Route::post('/admin/region/iglesias/actualizar', [RegionesController::class,'actualizarIglesia']);


// --- USUARIOS ---
// Usuarios por pais
Route::get('/admin/usuarios/pais/index', [UsuariosController::class,'indexUsuarioPais'])->name('admin.usuarios.pais.index');
Route::get('/admin/usuarios/pais/tabla', [UsuariosController::class,'tablaUsuarioPais']);

Route::get('/admin/usuarios/pais/todos/vista/{idpais}', [UsuariosController::class,'indexUsuariosPaisTodos']);
Route::get('/admin/usuarios/pais/todos/tabla/{idpais}', [UsuariosController::class,'tablaUsuariosPaisTodos']);
Route::post('/admin/usuarios/pais/info/usuario', [UsuariosController::class,'informacionUsuario']);

// --- IDIOMA SISTEMA ---


Route::get('/admin/idiomasistema/index', [PerfilController::class,'indexIdiomaSistema'])->name('admin.idioma.sistema.index');
Route::get('/admin/idiomasistema/tabla', [PerfilController::class,'tablaIdiomaSistema']);
Route::post('/admin/idiomasistema/nuevo', [PerfilController::class,'nuevoIdiomaSistema']);
Route::post('/admin/idiomasistema/informacion', [PerfilController::class,'informacionIdiomaSistema']);
Route::post('/admin/idiomasistema/actualizar', [PerfilController::class,'actualizarIdiomaSistema']);

// --- IMAGENES DEL DIA ---

Route::get('/admin/imagendia/index', [RecursosController::class,'indexImagenesDelDia'])->name('admin.imagenes.dia.index');
Route::get('/admin/imagendia/tabla', [RecursosController::class,'tablaImagenesDelDia']);
Route::post('/admin/imagendia/actualizar/posicion', [RecursosController::class,'actualizarPosicionImagenDia']);
Route::post('/admin/imagendia/nuevo', [RecursosController::class,'nuevaImagenDia']);
Route::post('/admin/imagendia/borrar', [RecursosController::class,'borrarImagenDia']);

// --- IMAGENES PARA PREGUNTAS ---

Route::get('/admin/imagenpreguntas/index', [RecursosController::class,'indexImagenesPreguntas'])->name('admin.imagenes.preguntas.index');
Route::get('/admin/imagenpreguntas/tabla', [RecursosController::class,'tablaImagenesPreguntas']);
Route::post('/admin/imagenpreguntas/nuevo', [RecursosController::class,'nuevaImagenPregunta']);
Route::post('/admin/imagenpreguntas/informacion', [RecursosController::class,'informacionImagenPregunta']);

Route::post('/admin/imagenpreguntas/actualizar', [RecursosController::class,'actualizarImagenPregunta']);


// --- PLANES ---
Route::get('/admin/planes/index', [PlanesController::class,'indexPlanes'])->name('admin.planes.index');
Route::get('/admin/planes/tabla', [PlanesController::class,'tablaPlanes']);
Route::get('/admin/planes/agregar/nuevo/index', [PlanesController::class,'indexNuevoPlan']);
Route::post('/admin/planes/agregar/nuevo', [PlanesController::class,'guardarNuevoPlan']);
Route::post('/admin/planes/actualizar/posicion', [PlanesController::class,'actualizarPosicionPlanes']);

// Editar plan
Route::get('/admin/planes/vista/editar/index/{idplan}', [PlanesController::class,'indexEditarPlan']);
Route::post('/admin/planes/datos/nuevo/idioma', [PlanesController::class,'actualizarPlanesIdiomaNuevo']); // agregar nuevo idioma
Route::post('/admin/planes/datos/actualizar', [PlanesController::class,'actualizarPlanes']); // actualizar todas las filas
Route::post('/admin/planes/imagen/actualizar', [PlanesController::class,'actualizarImagenPlanes']);
Route::post('/admin/planes/imagenportada/actualizar', [PlanesController::class,'actualizarImagenPortadaPlanes']);
Route::post('/admin/planes/imagen-ingles/actualizar', [PlanesController::class,'actualizarImagenInglesPlanes']);
Route::post('/admin/planes/imagenportada-ingles/actualizar', [PlanesController::class,'actualizarImagenPortadaInglesPlanes']);


// Eliminacion total de un devocional
Route::post('/admin/planes/borradototal', [PlanesController::class,'borradoTotalDevocional']);



// -> Vista Bloque de plan QUE SON LAS FECHAS
Route::get('/admin/planesbloques/vista/index/{idplan}', [PlanesController::class,'indexPlanBloque']);
Route::get('/admin/planesbloques/tabla/index/{idplan}', [PlanesController::class,'tablaPlanBloque']);
Route::get('/admin/planesbloques/agregar/nuevo/index/{idplan}', [PlanesController::class,'indexNuevoPlanBloque']);
Route::post('/admin/planesbloques/agregar/nuevo', [PlanesController::class,'registrarPlanesBloques']);
Route::post('/admin/planesbloques/borrarregistro', [PlanesController::class,'borrarRegistroPlanBloque']);





// Editar Bloque
Route::get('/admin/planesbloques/vista/editar/index/{idplanbloque}', [PlanesController::class,'indexEditarPlanBloque']);
Route::post('/admin/planesbloques/datos/actualizar', [PlanesController::class,'actualizarPlanesBloques']);

// -> Detalle del bloque
Route::get('/admin/planbloquedetalle/vista/{idplanbloque}', [PlanesController::class,'indexBloqueDetalle']);
Route::get('/admin/planbloquedetalle/tabla/{idplanbloque}', [PlanesController::class,'tablaBloqueDetalle']);
Route::get('/admin/planbloquedetalle/agregar/nuevo/index/{idplanbloque}', [PlanesController::class,'indexNuevoPlanBloqueDetalle']);
Route::post('/admin/planbloquedetalle/agregar/nuevo', [PlanesController::class,'registrarPlanesBloquesDetalle']);
Route::post('/admin/planbloquedetalle/actualizar/posicion', [PlanesController::class,'actualizarPosicionPlanesBlockDetalle']);
Route::post('/admin/planbloquedetalle/borrarregistro', [PlanesController::class,'borrarRegistroPlanBloqueDetalle']);


// Editar bloque detalle
Route::get('/admin/planbloquedetalle/vista/editar/index/{idplanbloquedetalle}', [PlanesController::class,'indexEditarPlanBloqueDetalle']);
Route::post('/admin/planbloquedetalle/datos/actualizar', [PlanesController::class,'actualizarPlanesBloquesDetaTextos']);

// Vista para agregar devocional
Route::get('/admin/planbloquedetalle/devocional/vista/{idplanbloquedetalle}', [PlanesController::class,'indexDevocionalPregunta']);
Route::post('/admin/planbloquedetalle/guardar/devocional', [PlanesController::class,'guardarDevocionalTexto']);
Route::post('/admin/planbloquedetalle/actualizar/devocional', [PlanesController::class,'actualizarDevocionalTexto']);

// -> Preguntas
Route::get('/admin/preguntas/vista/{idplanbloquedetalle}', [PreguntasController::class,'indexPreguntas']);
Route::get('/admin/preguntas/tabla/{idplanbloquedetalle}', [PreguntasController::class,'tablaPreguntas']);
Route::get('/admin/preguntas/nuevoregitros/{idplanbloquedetalle}', [PreguntasController::class,'indexNuevasPreguntas']);
Route::post('/admin/preguntas/registrar/nuevo', [PreguntasController::class,'registrarNuevaPregunta']);
Route::post('/admin/preguntas/actualizar/posicion', [PreguntasController::class,'actualizarPosicionPreguntas']);
Route::post('/admin/preguntas/borrar', [PreguntasController::class,'borrarPreguntaCompleta']);

// Preguntas por defecto en registro (18/05/2024)
Route::post('/admin/preguntas/registrar/nuevodefecto', [PreguntasController::class,'registrarNuevaPreguntaDefecto']);



// Meditacion
Route::get('/admin/preguntas/meditacion/vista/{idplanbloquedetalle}', [PreguntasController::class,'indexPreguntasMeditacion']);
Route::get('/admin/preguntas/meditacion/tabla/{idplanbloquedetalle}', [PreguntasController::class,'tablaPreguntasMeditacion']);

// Meditacion - Usuario
Route::get('/admin/preguntas/meditacion/usuario/{idplanbloquedetalle}/{idusuario}', [PreguntasController::class,'indexPreguntasMeditacionUsuario']);
Route::get('/admin/preguntas/meditacion/usuario/tabla/{idplanbloquedetalle}/{idusuario}', [PreguntasController::class,'tablaPreguntasMeditacionUsuario']);





// Editar
Route::get('/admin/preguntas/vista/editar/{idbloquepreguntas}', [PreguntasController::class,'indexEditarBloquePregunta']);
Route::post('/admin/preguntas/editar', [PreguntasController::class,'editarBloquePreguntas']);


// Informacion para compartir app
Route::get('/admin/comparteapp/index', [RecursosController::class,'indexComparteApp'])->name('admin.comparte.app.index');
Route::post('/admin/comparteapp/actualizar/imagen', [RecursosController::class,'actualizarImagenComparteApp']);
Route::post('/admin/comparteapp/registrar/idioma', [RecursosController::class,'registrarIdiomaComparteApp']);
Route::post('/admin/comparteapp/actualizar', [RecursosController::class,'actualizarComparteApp']);


// ---- VIDEOS HOY ---
Route::get('/admin/videoshoy/vista', [RecursosController::class,'indexVideosHoy'])->name('admin.videos.hoy.index');
Route::get('/admin/videoshoy/tabla', [RecursosController::class,'tablaVideosHoy']);
Route::get('/admin/videoshoy/vista/nuevo', [RecursosController::class,'vistaNuevoVideosHoy']);
Route::post('/admin/videoshoy/registrar', [RecursosController::class,'registrarVideoUrl']);
Route::post('/admin/videoshoy/actualizar/posicion', [RecursosController::class,'actualizarPosicionVideosHoy']);
Route::post('/admin/videoshoy/borrar', [RecursosController::class,'borrarVideoUrl']);

// editar
Route::get('/admin/videoshoy/vista/editar/{idvideohoy}', [RecursosController::class,'indexVideosHoyEditar']);
Route::post('/admin/videoshoy/imagen/actualizar', [RecursosController::class,'actualizarImagenVideosHoy']);
Route::post('/admin/videoshoy/actualizar', [RecursosController::class,'actualizarVideosHoyTextos']);


// --- DEVOCIONALES INICIO ---
Route::get('/admin/devoinicio/vista', [DevoInicioController::class,'indexDevoInicio'])->name('admin.devo.inicio.index');
Route::get('/admin/devoinicio/tabla', [DevoInicioController::class,'tablaDevoInicio']);
Route::post('/admin/devoinicio/borrar', [DevoInicioController::class,'borrarDevoInicio']);


// cargara todos los planes visibles para elegir
Route::get('/admin/devoinicio/planes/vista', [DevoInicioController::class,'indexDevoInicioPlanes']);
Route::get('/admin/devoinicio/planes/tabla', [DevoInicioController::class,'tablaDevoInicioPlanes']);

// cargara todos los bloques fechas
Route::get('/admin/devoinicio/planes/bloques/{idplan}', [DevoInicioController::class,'indexPlanesBloques']);
Route::get('/admin/devoinicio/planes/bloquestabla/{idplan}', [DevoInicioController::class,'tablaPlanesBloques']);

// cargara listado segun bloque fecha y se verificara si devocional esta creado
Route::get('/admin/devoinicio/planes/bloquesdetalle/{idplanbloque}', [DevoInicioController::class,'indexPlanesBloquesDetalle']);
Route::get('/admin/devoinicio/planes/bloquestabladetalle/{idplanbloque}', [DevoInicioController::class,'tablaPlanesBloquesDetalle']);
Route::post('/admin/devoinicio/informacion/devocional', [DevoInicioController::class,'infoDevocionalTexto']);
Route::post('/admin/devoinicio/seleccionar', [DevoInicioController::class,'seleccionarLecturaDia']);


// --- INSIGNIAS ---
Route::get('/admin/tipoinsignias/vista', [InsigniasController::class,'indexTipoInsignias'])->name('admin.tipoinsignias.index');
Route::get('/admin/tipoinsignias/tabla', [InsigniasController::class,'tablaTipoInsignias']);
Route::get('/admin/tipoinsignias/vistanuevo', [InsigniasController::class,'indexVistaNuevoTipoInsignias']);
Route::post('/admin/tipoinsignias/registrar', [InsigniasController::class,'registrarTipoInsignia']);

Route::get('/admin/tipoinsignias/vistaeditar/{idtipoinsignia}', [InsigniasController::class,'indexVistaEditarTipoInsignia']);

Route::post('/admin/tipoinsignias/actualizar/imagen', [InsigniasController::class,'actualizarImagen']);
Route::post('/admin/tipoinsignias/actualizar/datos', [InsigniasController::class,'actualizarTiposInsiginias']);

Route::get('/admin/tipoinsignias/vista/niveles/{idtipoinsignia}', [InsigniasController::class,'indexVistaInsigniaNiveles']);
Route::get('/admin/tipoinsignias/vista/tablaniveles/{idtipoinsignia}', [InsigniasController::class,'tablaVistaInsigniaNiveles']);
Route::post('/admin/tipoinsignias/niveles/registrar', [InsigniasController::class,'registrarNuevoNivel']);


// --- TEXTOS NOTIFICACIONES ---
Route::get('/admin/notificacion/vista', [RecursosController::class,'indexNotificacion'])->name('admin.notificacion.textos');
Route::get('/admin/notificacion/tabla', [RecursosController::class,'tablaNotificacion']);

Route::get('/admin/notificacion/vistaeditar/{idnoti}', [RecursosController::class,'indexNotificacionEditar']);
Route::post('/admin/notificacion/borrar/imagen', [RecursosController::class,'borrarImagenTipoNotificacion']);
Route::post('/admin/notificacion/imagen/actualizar', [RecursosController::class,'actualizarImagenTipoNotificacion']);
Route::post('/admin/notificacion/actualizar/textos', [RecursosController::class,'actualizarTextosNotificacion']);




// --- BIBLIAS ---

Route::get('/admin/biblia/vista', [BibliasController::class,'vistaBiblias'])->name('admin.biblias');
Route::get('/admin/biblia/tabla', [BibliasController::class,'tablaBiblias']);
Route::post('/admin/biblia/registrar', [BibliasController::class,'registrarBiblia']);
Route::post('/admin/biblia/informacion', [BibliasController::class,'informacionBiblia']);
Route::post('/admin/biblia/actualizar', [BibliasController::class,'actualizarBiblia']);
Route::post('/admin/biblia/activacion', [BibliasController::class,'estadoBiblia']);
Route::post('/admin/biblias/actualizar/posicion', [BibliasController::class,'actualizarPosicionBiblias']);


// --- BIBLIAS LIBROS ---
Route::get('/admin/biblialibro/vista/{idbiblia}', [BibliaCapituloController::class,'vistaLibro']);
Route::get('/admin/biblialibro/tabla/{idbiblia}', [BibliaCapituloController::class,'tablaLibro']);
Route::post('/admin/biblialibro/registrar', [BibliaCapituloController::class,'registrarLibro']);
Route::post('/admin/biblialibro/posicion', [BibliaCapituloController::class,'actualizarPosicionBibliaLibros']);
Route::post('/admin/biblialibro/informacion', [BibliaCapituloController::class,'informacionLibro']);
Route::post('/admin/biblialibro/actualizar', [BibliaCapituloController::class,'actualizarLibro']);
Route::post('/admin/biblialibro/activacion', [BibliaCapituloController::class,'estadoLibro']);


// --- BIBLIA CAPITULOS - BLOQUES ---
Route::get('/admin/bibliacapitulo/bloque/vista/{idcapitulo}', [BibliaCapituloController::class,'vistaCapitulosBloque']);
Route::get('/admin/bibliacapitulo/bloque/tabla/{idcapitulo}', [BibliaCapituloController::class,'tablaCapitulosBloque']);
Route::post('/admin/bibliacapitulo/bloque/registrar', [BibliaCapituloController::class,'registrarCapituloBloque']);
Route::post('/admin/bibliacapitulo/bloque/informacion', [BibliaCapituloController::class,'informacionCapituloBloque']);
Route::post('/admin/bibliacapitulo/bloque/actualizar', [BibliaCapituloController::class,'actualizarCapituloBloque']);
Route::post('/admin/bibliacapitulo/bloque/activacion', [BibliaCapituloController::class,'estadoCapituloBloque']);
Route::post('/admin/bibliacapitulo/bloque/posicion', [BibliaCapituloController::class,'actualizarPosicionBibliaCapitulosBloque']);


// --- BUSQUEDA TEXTO CAPITULO ---
Route::post('/admin/bibliacapitulo/informacion/texto', [BibliaCapituloController::class,'busquedaTextoCapitulo']);
Route::post('/admin/bibliacapitulo/texto/actualizar', [BibliaCapituloController::class,'guardarTextoVersiculo']);










// --- DEVOCIONAL BIBLIAS ---

Route::get('/admin/devobiblia/vista/{idbloqedetalle}', [DevoBibliaController::class,'vistaDevoBiblia']);
Route::get('/admin/devobiblia/tabla/{idbloqedetalle}', [DevoBibliaController::class,'tablaDevoBiblia']);
Route::post('/admin/devobiblia/registrar', [DevoBibliaController::class,'registrarBibliaDevo']);
Route::post('/admin/devobiblia/borrarregistro', [DevoBibliaController::class,'borrarRegistroDevoBiblia']);

// vista para registrar libro capitulo
Route::get('/admin/devobiblia/capitulos/vista/{iddevobiblia}', [DevoBibliaController::class,'vistaDevoBibliaCapitulos']);
Route::get('/admin/devobiblia/capitulos/tabla/{iddevobiblia}', [DevoBibliaController::class,'tablaDevoBibliaCapitulos']);
Route::post('/admin/devobiblia/buscador/capitulos', [DevoBibliaController::class,'buscadorCapitulos']);

Route::post('/admin/devobiblia/registrar/capitulos', [DevoBibliaController::class,'registrarCapitulo']);
Route::post('/admin/devobiblia/borrar/fila', [DevoBibliaController::class,'borrarFila']);







