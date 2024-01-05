<?php

namespace App\Http\Controllers\Api\Registro;

use App\Http\Controllers\Controller;
use App\Models\UsuarioNotificaciones;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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

        DB::beginTransaction();


        try {

            if(Usuarios::where('correo', $request->correo)->first()){
                return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
            }

            $fecha = Carbon::now('America/El_Salvador');

            $usuario = new Usuarios();
            $usuario->id_iglesia = $request->iglesia;
            $usuario->id_genero = $request->genero;
            $usuario->nombre = $request->nombre;
            $usuario->apellido = $request->apellido;
            $usuario->fecha_nacimiento = $request->edad;
            $usuario->correo = $request->correo;
            $usuario->password = Hash::make($request->password);
            $usuario->version_registro = $request->version;
            $usuario->fecha_registro = $fecha;
            $usuario->save();

            $ajustes = new UsuarioNotificaciones();
            $ajustes->id_usuario = $usuario->id;
            $ajustes->onesignal = $request->onesignal;
            $ajustes->notificacion_general = 1;
            $ajustes->save();

            DB::commit();
            return ['success' => 2, 'id' => $usuario->id];

        }catch(\Throwable $e){
            Log::info('Error Registro Usuario ' . $e);
            DB::rollback();
            return ['success' => 99];
        }




    }
}
