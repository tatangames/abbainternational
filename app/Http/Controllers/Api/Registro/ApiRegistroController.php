<?php

namespace App\Http\Controllers\Api\Registro;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiRegistroController extends Controller
{

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

                return ['success' => 1,
                    'id' => strval($infoUsuario->id),
                    'mensaje' => "Inicio de sesion correctamente",
                ];

            }else{
                // contraseña incorrecta

                return ['success' => 2, 'mensaje' => "Contrasena incorrecta"];
            }

        } else {

            return ['success' => 2, 'mensaje' => "Correo no encontrado"];
        }
    }


    public function registroUsuario(Request $request)
    {

        if(Usuarios::where('correo', $request->correo)->first()){
            return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
        }

        $usuario = new Usuarios();
        $usuario->id_iglesia = $request->iglesia;
        $usuario->id_genero = $request->genero;
        $usuario->nombre = $request->nombre;
        $usuario->apellido = $request->apellido;
        $usuario->edad = $request->edad;
        $usuario->correo = $request->correo;
        $usuario->password = Hash::make($request->password);
        $usuario->version_registro = $request->version;
        $usuario->recibir_notificacion = 1;
        $usuario->onesignal = $request->onesignal;

        if($usuario->save()){
            return ['success' => 2, 'id' => $usuario->id];
        }else{
            return ['success' => 99];
        }
    }
}
