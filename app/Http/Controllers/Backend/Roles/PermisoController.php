<?php

namespace App\Http\Controllers\Backend\Roles;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoController extends Controller
{

    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function index(){
        $roles = Role::all()->pluck('name', 'id');

        return view('backend.admin.rolesypermisos.permisos', compact('roles'));
    }

    public function tablaUsuarios(){
        $usuarios = Administrador::orderBy('id', 'ASC')->get();

        return view('backend.admin.rolesypermisos.tabla.tablapermisos', compact('usuarios'));
    }

    public function nuevoUsuario(Request $request){

        if(Administrador::where('email', $request->correo)->first()){
            return ['success' => 1];
        }

        $u = new Administrador();
        $u->nombre = $request->nombre;
        $u->email = $request->correo;
        $u->password = Hash::make($request->password);

        if ($u->save()) {
            $role = Role::findById($request->rol, 'api');
            $u->assignRole($role);

            return ['success' => 2];
        } else {
            return ['success' => 3];
        }
    }

    public function infoUsuario(Request $request){
        if($info = Administrador::where('id', $request->id)->first()){

            $roles = Role::all()->pluck('name', 'id');

            $idrol = $info->roles->pluck('id');

            return ['success' => 1,
                'info' => $info,
                'roles' => $roles,
                'idrol' => $idrol];

        }else{
            return ['success' => 2];
        }
    }

    public function editarUsuario(Request $request){

        if(Administrador::where('id', $request->id)->first()){

            if(Administrador::where('email', $request->correo)
                ->where('id', '!=', $request->id)->first()){
                return ['success' => 1];
            }

            $usuario = Administrador::find($request->id);
            $usuario->nombre = $request->nombre;
            $usuario->email= $request->correo;

            if($request->password != null){
                $usuario->password = Hash::make($request->password);
            }

            $role = Role::findById($request->rol, 'api');
            $usuario->syncRoles($role);

            $usuario->save();

            return ['success' => 2];
        }else{
            return ['success' => 3];
        }
    }

    public function nuevoRol(Request $request){

        $regla = array(
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        // verificar si existe el rol
        if(Role::where('name', $request->nombre)->first()){
            return ['success' => 1];
        }

        Role::create(['name' => $request->nombre, 'guard_name' => 'api']);

        return ['success' => 2];
    }

    public function nuevoPermisoExtra(Request $request){

        // verificar si existe el permiso
        if(Permission::where('name', $request->nombre)->first()){
            return ['success' => 1];
        }

        Permission::create(['name' => $request->nombre, 'guard_name' => 'api', 'description' => $request->descripcion]);

        return ['success' => 2];
    }

    public function borrarPermisoGlobal(Request $request){

        // buscamos el permiso el cual queremos eliminar
        $permission = Permission::findById($request->idpermiso)->delete();

        return ['success' => 1];
    }


}
