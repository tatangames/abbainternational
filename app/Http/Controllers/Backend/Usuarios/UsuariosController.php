<?php

namespace App\Http\Controllers\Backend\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\Pais;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de paises para ver usuarios
    public function indexUsuarioPais(){
        return view('backend.admin.usuarios.pais.vistausuariopais');
    }

    // regresa tabla de paises para ver usuarios
    public function tablaUsuarioPais(){

        $listado = Pais::orderBy('nombre', 'ASC')->get();

        foreach ($listado as $dato){

            $arrayDepa = Departamentos::where('id_pais', $dato->id)
                ->select('id')
                ->get();
            $conteoDepa = count($arrayDepa);

            $arrayIglesia = Iglesias::whereIn('id_departamento', $arrayDepa)
                ->select('id')
                ->get();
            $conteoIglesia = count($arrayIglesia);


            $conteoUsuarios = Usuarios::whereIn('id_iglesia', $arrayIglesia)->count();


            $dato->conteodepa = $conteoDepa;
            $dato->conteoigle = $conteoIglesia;
            $dato->conteousuario = $conteoUsuarios;
        }

        return view('backend.admin.usuarios.pais.tablausuariopais', compact('listado'));
    }


    // retorna vista de todos los usuarios asignados a un pais
    public function indexUsuariosPaisTodos($idpais){

    }

    // retorna tabla de todos los usuarios asignados a un pais
    public function tablaUsuariosPaisTodos($idpais){

        $arrayDepaID = Departamentos::where('id_pais', $idpais)
            ->select('id')
            ->get();

        $arrayUsuarios = DB::table('usuarios AS u')
            ->join('iglesia AS igle', 'u.id_iglesia', '=', 'igle.id')
            ->select('igle.id_departamento', 'igle.nombre', )
            ->whereIn('igle.id_departamento', '=', $arrayDepaID)
            ->get();

            foreach ($arrayUsuarios as $dato){
                $infoDepa = Departamentos::where('id', $dato->id_departamento)->first();

                $dato->nombredepa = $infoDepa->nombre;
            }



        }


}
