<?php

namespace App\Http\Controllers\Api\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;


class ApiPerfilController extends Controller
{


    // informacion de mi perfil
    public function informacionPerfilUsuario(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $fechaNac = date("d-m-Y", strtotime($userToken->fecha_nacimiento));

            return ['success' => 1,
                'nombre' => $userToken->nombre,
                'apellido' => $userToken->apellido,
                'fecha_nacimiento' => $fechaNac,
                'correo' => $userToken->correo,
                'fecha_nac_raw' => $userToken->fecha_nacimiento
            ];
        }else{
            return ['success' => 2];
        }
    }

    // cuabre se abre fragment ajustes
    public function informacionAjustes(Request $request){

        $rules = array(
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $info = Usuarios::where('id', $userToken->id)->first();

            $primeraLetra = substr($info->nombre, 0, 1);

            return ['success' => 1,
                'letra' => $primeraLetra,
                'nombre' => $info->nombre . " " . $info->apellido,
            ];

        }else{
            return ['success' => 2];
        }
    }


    // actualizar datos de mi perfil
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

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if(Usuarios::where('correo', $request->correo)
                ->where('id', '!=', $userToken->id)->first()){
                return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
            }

            // actualizar

            Usuarios::where('id', $userToken->id)
                ->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'fecha_nacimiento' => $request->fechanac,
                    'correo' => $request->correo
                    ]);

            return ['success' => 2];

        }else{
            return ['success' => 99];
        }
    }


    // actualizar perfil utilizando token
    public function actualizarPassword(Request $request)
    {
        $rules = array(
            'password' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            Usuarios::where('id', $userToken->id)->update([
                'password' => Hash::make($request->password)
            ]);

            // usuario cambio password
            return ['success' => 1];

        }else{
            // usuario no encontrado
            return ['success' => 2];
        }
    }







}
