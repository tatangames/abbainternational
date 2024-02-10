<?php

namespace App\Http\Controllers\api\Comunidad;

use App\Http\Controllers\Controller;
use App\Models\ComunidadSolicitud;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\InsigniasUsuarios;
use App\Models\Pais;
use App\Models\Usuarios;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiComunidadController extends Controller
{

    // enviar solicitud a un amigo
    public function enviarSolicitudAmigo(Request $request){

        $rules = array(
            'iduser' => 'required',
            'correo' => 'required',
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            // buscar usuario del correo
            if($infoEncontrado = Usuarios::where('correo', $request->correo)
                ->where('id', '!=', $userToken->id)
                ->first()){

                if(ComunidadSolicitud::where('id_usuario', $userToken->id)
                    ->where('id_usuario_recibe', $infoEncontrado->id)->first()){

                    // solo decir que se envio solicitud

                    return ['success' => 2,
                            'msg' => "solicitud enviada"];
                }else{
                    // registrar y enviar notificacion en segundo plano

                    $fechaActual = $this->retornoZonaHorariaUsuario($userToken->id_iglesia);

                    $nuevo = new ComunidadSolicitud();
                    $nuevo->id_usuario_envia = $userToken->id;
                    $nuevo->id_usuario_recibe = $infoEncontrado->id;
                    $nuevo->fecha = $fechaActual;
                    $nuevo->estado = 0;
                    $nuevo->save();
                }

                return ['success' => 2,
                    'msg' => "solicitud enviada"];
            }else{
                return ['success' => 1,
                        'msg' => "Correo no encontrado"];
            }
        }
        else{
            return ['success' => 99];
        }
    }

    // listado de solicutes pendientes que yo he enviado
    public function listadoSolicitudesPendientes(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            $arrayPendientes = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                ->where('estado', 0)
                ->orderBy('fecha', 'DESC')
                ->get();

            foreach ($arrayPendientes as $dato){
                $infoUsuario = Usuarios::where('id', $dato->id)->first();

                $dato->nombre = $infoUsuario->nombre;
            }

            return ['success' => 1,
                'listado' => $arrayPendientes];
        }
        else{
            return ['success' => 99];
        }
    }


    // listado de solicutes aceptadas que yo he enviado
    public function listadoSolicitudesAceptadas(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            $arrayAceptados = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                ->where('estado', 1) // slo aceptadas
                ->get();

            foreach ($arrayAceptados as $dato){
                $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();
                $infoIglesia = Iglesias::where('id', $infoUsuario->id_iglesia)->first();
                $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
                $infoPais = Pais::where('id', $infoDepartamento->id_pais)->first();

                $nombreFull = $infoUsuario->nombre;
                if($infoUsuario->apellido != null){
                    $nombreFull = $nombreFull . " " . $infoUsuario->apellido;
                }

                $dato->nombre = $nombreFull;
                $dato->iglesia = $infoIglesia->nombre;
                $dato->correo = $infoUsuario->correo;
                $dato->pais = $infoPais->nombre;
            }

            // ordenar array por correo
            $arrayAceptadosSort = $arrayAceptados->sortBy('correo')->values();


            return ['success' => 1,
                'listado' => $arrayAceptadosSort];
        }
        else{
            return ['success' => 99];
        }
    }


    public function eliminarSolicitud(Request $request){

        $rules = array(
            'idsolicitud' => 'required',
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            // borrar solicitud de amistad
            if(ComunidadSolicitud::where('id', $request->idsolicitud)->first()){
                ComunidadSolicitud::where('id', $request->idsolicitud)->delete();
            }

            return ['success' => 1];
        }
        else{
            return ['success' => 99];
        }
    }

    public function informacionInsigniaAmigo(Request $request){

        $rules = array(
            'idsolicitud' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if($infoComu = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){

                $arrayInsignias = InsigniasUsuarios::where('id_usuario', $infoComu->id_usuario_recibe)->get();

                foreach ($arrayInsignias as $dato){

                }

                return ['success' => 1,
                    'listado' => $arrayInsignias];

            }else{
                return ['success' => 99];
            }
        }
        else{
            return ['success' => 99];
        }

    }



    private function retornoZonaHorariaUsuario($idIglesia){
        $infoIglesia = Iglesias::where('id', $idIglesia)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();
        $zonaHoraria = $infoZonaHoraria->zona;

        // horario actual del cliente segun zona horaria

        return Carbon::now($zonaHoraria);
    }



    // COMO IDIOMATEXTOS DEVUELVE 0 POR DEFECTO, Y EL ID 1 ES MINIMO EN LA BASE DE DATOS
    private function reseteoIdiomaTextos($idiomatextos)
    {
        if($idiomatextos == 0){
            $idiomatextos = 1;
        }

        return $idiomatextos;
    }
}
