<?php

namespace App\Http\Controllers\Backend\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('backend.admin.rolesypermisos.roles');
    }

    public function tablaRoles(){
        $roles = Role::all()->pluck('name', 'id');
        return view('backend.admin.rolesypermisos.tabla.tablaroles', compact('roles'));
    }

    public function vistaPermisos($id){
        // obtener todos los permisos que existen
        $permisos = Permission::all()->sortBy('name')->pluck('name', 'id');

        return view('backend.admin.rolesypermisos.rolespermisos', compact('id', 'permisos'));
    }

    public function tablaRolesPermisos($id){
        // se recibe el id del Rol, para buscar los permisos agregados a este Rol.

        //$role = Role::findById(1, 'api');

        // lista de permisos asignados al Rol
        $permisos = Role::findById($id, 'api')->permissions()->pluck('name', 'id');

        // lista de todos los permisos que existen
        //$permisos = Permission::pluck('name', 'id');

        return view('backend.admin.rolesypermisos.tabla.tablarolespermisos', compact('permisos'));
    }

    public function borrarPermiso(Request $request){

        // buscamos el Permiso por su ID
        $permission = Permission::findById($request->idpermiso, 'api');

        // buscamos el Rol a cual le quitaremos el permiso
        $role = Role::findById($request->idrol, 'api');

        // quitamos el permiso al Rol
        $role->revokePermissionTo($permission);

        return ['success' => 1];
    }

    public function agregarPermiso(Request $request){

        // buscamos el Rol a cual le queremos agregar el permiso
        $role = Role::findById($request->idrol, 'api');

        // buscamos el permiso el cual queremos agregar
        $permission = Permission::findById($request->idpermiso, 'api');

        // asignamos el permiso al Rol
        $role->givePermissionTo($permission);

        return ['success' => 1];
    }

    public function listaTodosPermisos(){
        return view('backend.admin.rolesypermisos.listapermisos');
    }

    public function tablaTodosPermisos(){

        $permisos = Permission::all();
        return view('backend.admin.rolesypermisos.tabla.tablalistapermisos', compact('permisos'));
    }

    public function borrarRolGlobal(Request $request){

        // buscar el rol por id
        $role = Role::findById($request->idrol, 'api');

        // elimina el rol y todos los permisos asociados
        $role->delete();

        return ['success' => 1];
    }
}
