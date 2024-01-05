<?php

namespace App\Http\Controllers\Api\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiPerfilController extends Controller
{

    public function informacionPerfilUsuario(Request $request)
    {

        if($info = Usuarios::where('id', $request->iduser)->first()){

            $fechaNac = date("d-m-Y", strtotime($info->fecha_nacimiento));

            return ['success' => 1,
                'nombre' => $info->nombre,
                'apellido' => $info->apellido,
                'fecha_nacimiento' => $fechaNac,
                'correo' => $info->correo,
                'fecha_nac_raw' => $info->fecha_nacimiento
            ];
        }

        return ['success' => 2];
    }


    public function informacionAjustes(Request $request){

        if($info = Usuarios::where('id', $request->iduser)->first()){

            $primeraLetra = substr($info->nombre, 0, 1);

            return ['success' => 1,
                'letra' => $primeraLetra,
                'nombre' => $info->nombre . " " . $info->apellido,
            ];
        }

        return ['success' => 2];
    }



    public function actualizarPerfilUsuario(Request $request){

        $rules = array(
            'iduser' => 'required',
            'nombre' => 'required',
            'apellido' => 'required',
            'fechanac' => 'required',
            'correo' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(Usuarios::where('correo', $request->correo)
            ->where('id', '!=', $request->iduser)->first()){
            return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
        }


        return ['success' => 2];
    }


}
