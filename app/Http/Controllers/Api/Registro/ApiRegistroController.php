<?php

namespace App\Http\Controllers\Api\Registro;

use App\Http\Controllers\Controller;
use App\Models\Iglesias;
use App\Models\RachaAlta;
use App\Models\UsuarioNotificaciones;
use App\Models\UsuarioRegionOtros;
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

    public function registroUsuarioV2(Request $request)
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

        // idonesignal, paisotros, ciudadotros

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
            $nuevoUsuario->idioma_noti = 1; // defecto espanol
            $nuevoUsuario->imagen = null;
            $nuevoUsuario->onesignal = $request->idonesignal; // puede ser null
            $nuevoUsuario->save();

            // SELECCIONO PAIS OTROS
            if($request->iglesia == 503){

                $detalle = new UsuarioRegionOtros();
                $detalle->id_usuario = $nuevoUsuario->id;
                $detalle->pais = $request->paisotros;
                $detalle->ciudad = $request->ciudadotros;
                $detalle->save();
            }



            $token = JWTAuth::fromUser($nuevoUsuario);


            // REGISTRAR RACHA ALTA POR DEFECTO
            $rachaAlta = new RachaAlta();
            $rachaAlta->id_usuarios = $nuevoUsuario->id;
            $rachaAlta->contador = 0;
            $rachaAlta->save();




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




    public function listadoDeIglesias(Request $request){

        $rules = array(
            'iddepa' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'error' => 'validacion incorrecta'];
        }

        $listado = Iglesias::where('id_departamento', $request->iddepa)
            ->where('visible', 1)
            ->get();

        return ['success' => 1,
                'listado' => $listado];
    }



}
