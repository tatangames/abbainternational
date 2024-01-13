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
use Tymon\JWTAuth\Facades\JWTAuth;


class ApiRegistroController extends Controller
{

    // registro de usuario
    public function registroUsuario(Request $request)
    {
        $rules = array(
            'nombre' => 'required',
            'apellido' => 'required',
            'edad' => 'required',
            'genero' => 'required',
            'iglesia' => 'required',
            'correo' => 'required',
            'password' => 'required',
            'version' => 'required',
        );

        // onesignal

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'error' => 'validacion incorrecta'];
        }


        DB::beginTransaction();


        try {

            // verificar que el correo no este registrado ya
            if(Usuarios::where('correo', $request->correo)->first()){
                return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
            }


            $fecha = Carbon::now('America/El_Salvador');

            $nuevoUsuario = new Usuarios();
            $nuevoUsuario->id_iglesia = $request->iglesia;
            $nuevoUsuario->id_genero = $request->genero;
            $nuevoUsuario->nombre = $request->nombre;
            $nuevoUsuario->apellido = $request->apellido;
            $nuevoUsuario->fecha_nacimiento = $request->edad;
            $nuevoUsuario->correo = $request->correo;
            $nuevoUsuario->password = Hash::make($request->password);
            $nuevoUsuario->version_registro = $request->version;
            $nuevoUsuario->fecha_registro = $fecha;
            $nuevoUsuario->save();

            $token = JWTAuth::fromUser($nuevoUsuario);

            $ajustes = new UsuarioNotificaciones();
            $ajustes->id_usuario = $nuevoUsuario->id;
            $ajustes->onesignal = $request->onesignal;
            $ajustes->notificacion_general = 1;
            $ajustes->save();

            DB::commit();
            return ['success' => 2,
                'id' => $nuevoUsuario->id,
                'token' => $token];

        }catch(\Throwable $e){
            Log::info('Error Registro Usuario ' . $e);
            DB::rollback();
            return ['success' => 99,
                'error' => $e];
        }
    }
}
