<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Backend\Dashboard\DashboardController;
use App\Http\Controllers\Controles\ControlRolController;
use App\Http\Controllers\Backend\Roles\PermisoController;
use App\Http\Controllers\Backend\Roles\RolesController;
use App\Http\Controllers\Backend\Sistema\PerfilController;


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









Route::get('/admin/dashboard/index', [DashboardController::class,'index'])->name('admin.dashboard.index');
