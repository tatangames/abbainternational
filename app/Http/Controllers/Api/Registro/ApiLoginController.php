<?php

namespace App\Http\Controllers\Api\Registro;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiLoginController extends Controller
{

    // inicio de sesion
    public function loginUsuario(Request $request)
    {
        $rules = array(
            'correo' => 'required',
            'password' => 'required',
        );

        $validator = Validator::make($request->all(), $rules );

        if ( $validator->fails()){
            return ['success' => 0];
        }

        if($infoUsuario = Usuarios::where('correo', $request->correo)->first()){

            if (Hash::check($request->password, $infoUsuario->password)) {

                try {

                    $token = JWTAuth::fromUser($infoUsuario);


                    return ['success' => 1,
                        'id' => strval($infoUsuario->id),
                        'token' => $token,
                        'mensaje' => "Inicio de sesion correctamente",
                    ];

                } catch (JWTException $e){
                    return ['success' => 2, 'mensaje' => "Exception JWT"];
                }

            }else{
                // contraseña incorrecta

                return ['success' => 2, 'mensaje' => "Contrasena incorrecta"];
            }

        } else {

            return ['success' => 2, 'mensaje' => "Correo no encontrado"];
        }
    }








}
