<?php

namespace App\Http\Controllers\Api\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiPerfilController extends Controller
{

    /*public function __construct()
    {
        # By default we are using here auth:api middleware
        $this->middleware('auth:api', ['except' => ['informacionAjustes']]);
    }*/

    public function informacionPerfilUsuario(Request $request)
    {

        try {
            // Intenta obtener el usuario autenticado
            if ($usuario = JWTAuth::user()) {
                // El token es válido y se ha encontrado un usuario
                return response()->json(['usuario' => $usuario->nombre]);
            } else {
                // El token es válido, pero no se encontró un usuario (puede ser un token de acceso inválido)
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
        } catch (JWTException $e) {
            // Captura excepciones relacionadas con JWT
            return response()->json(['error' => 'Error al procesar el token'], 500);
        }



        return "entraa";

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
