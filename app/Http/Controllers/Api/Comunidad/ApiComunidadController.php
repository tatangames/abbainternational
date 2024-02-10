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

                if($infoComu = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                    ->where('id_usuario_recibe', $infoEncontrado->id)->first()){

                    if($infoComu->estado == 0){
                        // solicitud pendiente de aceptacion
                        return ['success' => 1, 'msg' => "solicitud esta pendiente de aceptacion"];
                    }else{
                        // solitud ya esta aceptada
                        return ['success' => 1, 'msg' => "solicitud ya esta aceptada"];
                    }

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

                return ['success' => 3,
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
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $arrayPendientes = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                ->where('estado', 0)
                ->orderBy('fecha', 'DESC')
                ->get();


            // los datos que vera el usuario son: correo, enviada

            foreach ($arrayPendientes as $dato){
                $infoUsuario = Usuarios::where('id', $dato->id)->first();

                $dato->correo = $infoUsuario->correo;
                $fecha = date("d-m-Y", strtotime($dato->fecha));
                $dato->fecha = $fecha;
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
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $arrayAceptados = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                ->where('estado', 1) // slo aceptadas
                ->get();

            foreach ($arrayAceptados as $dato){
                $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();
                $infoIglesia = Iglesias::where('id', $infoUsuario->id_iglesia)->first();
                $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
                $infoPais = Pais::where('id', $infoDepartamento->id_pais)->first();

                // siempre es requerido apellido
                $nombreFull = $infoUsuario->nombre . " " . $infoUsuario->apellido;

                $dato->nombre = $nombreFull;
                $dato->iglesia = $infoIglesia->nombre;
                $dato->correo = $infoUsuario->correo;
                $dato->pais = $infoPais->nombre;
                $dato->idpais = $infoPais->id;
            }

            $hayinfo = 0;
            if($arrayAceptados != null && $arrayAceptados->isNotEmpty()){
                $hayinfo = 1;
            }

            // ordenar array por correo
            $arrayAceptadosSort = $arrayAceptados->sortBy('correo')->values();


            return ['success' => 1,
                'hayinfo' => $hayinfo,
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
            if($info = ComunidadSolicitud::where('id', $request->idsolicitud)
                ->where('id_usuario_envia', $userToken->id)->first()){

                ComunidadSolicitud::where('id', $info->id)->delete();
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
        $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();
        return Carbon::now($infoZonaHoraria->zona);
    }



}
