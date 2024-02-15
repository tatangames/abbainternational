<?php

namespace App\Http\Controllers\api\Comunidad;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacion;
use App\Models\ComunidadSolicitud;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\InsigniasUsuarios;
use App\Models\NotificacionTextos;
use App\Models\NotificacionUsuario;
use App\Models\Pais;
use App\Models\UsuarioNotificaciones;
use App\Models\Usuarios;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OneSignal;


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

            DB::beginTransaction();

            try {

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
                            return ['success' => 2, 'msg' => "solicitud ya esta aceptada"];
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


                        // INSIGNIA COMPARTIR DEVOCIONAL
                        $notiHistorial = new NotificacionUsuario();
                        $notiHistorial->id_usuario = $infoEncontrado->id;
                        $notiHistorial->id_tipo_notificacion = 11;
                        $notiHistorial->fecha = $fechaActual;
                        $notiHistorial->save();


                        $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $infoEncontrado->id)->get();
                        $pilaOneSignal = array();
                        $hayIdOne = false;
                        foreach ($arrayOneSignal as $item){
                            if($item->onesignal != null){
                                $hayIdOne = true;
                                array_push($pilaOneSignal, $item->onesignal);
                            }
                        }

                        if($hayIdOne){
                            // UN AMIGO TE ACABA DE ENVIAR UNA SOLICITUD
                            $datosRaw = $this->retornoTitulosNotificaciones(11, $infoEncontrado->idioma_noti);
                            $tiNo = $datosRaw['titulo'];
                            $desNo = $datosRaw['descripcion'];

                            // como es primera vez, se necesita enviar notificacion
                            dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                        }
                    }

                    return ['success' => 3,
                        'msg' => "solicitud enviada"];
                }else{
                    return ['success' => 4,
                        'msg' => "Correo no encontrado"];
                }


            }catch(\Throwable $e){
                Log::info("error" . $e);
                DB::rollback();
                return ['success' => 99];
            }
        }
        else{
            return ['success' => 99];
        }
    }




    // RETORNO TITULO Y DESCRIPCION PARA NOTIFICACIONES
    private function retornoTitulosNotificaciones($idTipoNotificacion, $idiomaTexto){

        if($infoTexto = NotificacionTextos::where('id_tipo_notificacion', $idTipoNotificacion)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return ['titulo' => $infoTexto->titulo,
                'descripcion' => $infoTexto->descripcion,
            ];

        }else{

            // si no encuentra sera por defecto español
            $infoTexto = NotificacionTextos::where('id_tipo_notificacion', $idTipoNotificacion)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['titulo' => $infoTexto->titulo,
                'descripcion' => $infoTexto->descripcion,
            ];
        }
    }





    // listado de solicutes pendientes que yo he enviado
    public function listadoSolicitudesPendientesEnviadas(Request $request){

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

            $hayinfo = 0;

            Log::info("token: " . $userToken->id);

            $arrayPendientes = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                ->where('estado', 0)
                ->orderBy('fecha', 'DESC')
                ->get();


            // los datos que vera el usuario son: correo, enviada

            foreach ($arrayPendientes as $dato){
                $hayinfo = 1;

                $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();

                $dato->correo = $infoUsuario->correo;
                $fecha = date("d-m-Y", strtotime($dato->fecha));
                $dato->fecha = $fecha;
            }

            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayPendientes];
        }
        else{
            return ['success' => 99];
        }
    }



    public function listadoSolicitudesPendientesRecibidas(Request $request){

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

            $hayinfo = 0;

            $arrayPendientes = ComunidadSolicitud::where('id_usuario_recibe', $userToken->id)
                ->where('estado', 0)
                ->orderBy('fecha', 'DESC')
                ->get();


            // los datos que vera el usuario son: correo, enviada

            foreach ($arrayPendientes as $dato){
                $hayinfo = 1;

                $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();

                $dato->correo = $infoUsuario->correo;
                $fecha = date("d-m-Y", strtotime($dato->fecha));
                $dato->fecha = $fecha;
            }

            return ['success' => 1,
                'hayinfo' => $hayinfo,
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

            $a = $userToken->id;

            $arrayAceptados = DB::table('comunidad_solicitud')
                ->where(function ($query) use ($a) {
                    $query->where('id_usuario_envia', $a)
                        ->orWhere('id_usuario_recibe', $a);
                })
                ->where('estado', 1)
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

            // borrar solicitud de amistad, exista o no
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



    public function listadoNotificaciones(Request $request){
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'page' => 'required',
            'limit' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {

            $hayInfo = 0;
            $conteoNoti = NotificacionUsuario::where('id_usuario', $userToken->id)
                ->orderBy('fecha', 'DESC')
                ->count();

            if ($conteoNoti != null && $conteoNoti > 0) {
                $hayInfo = 1;
            }

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            $arrayNotificacion = NotificacionUsuario::where('id_usuario', $userToken->id)
                ->orderBy('fecha', 'DESC')
                ->paginate($limit, ['*'], 'page', $page);

            foreach ($arrayNotificacion as $dato){

               $arrayRaw = $this->retornoTituloNotificacion($dato->id_tipo_notificacion, $idiomaTextos);
               $dato->titulo = $arrayRaw['descripcion'];
            }

            return [
                'success' => 1,
                'hayinfo' => $hayInfo,
                'listado' => $arrayNotificacion
            ];
        }else{
            return ['success' => 99];
        }
    }

    private function retornoTituloNotificacion($idtiponoti, $idiomaplan){

        if($infoTexto = NotificacionTextos::where('id_tipo_notificacion', $idtiponoti)
            ->where('id_idioma_planes', $idiomaplan)->first()){

            return ['titulo' => $infoTexto->titulo,
                    'descripcion' => $infoTexto->descripcion];
        }else{
            $infoTexto = NotificacionTextos::where('id_tipo_notificacion', $idtiponoti)
                ->where('id_idioma_planes', 1)->first();

            return ['titulo' => $infoTexto->titulo,
                'descripcion' => $infoTexto->descripcion];
        }
    }


    private function retornoZonaHorariaUsuario($idIglesia){
        $infoIglesia = Iglesias::where('id', $idIglesia)->first();
        $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();
        return Carbon::now($infoZonaHoraria->zona);
    }



    public function aceptarSolicitudRecibido(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
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

            // PASAR A ESTADO 0
            if($info = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){

                ComunidadSolicitud::where('id', $info->id)
                    ->update(['estado' => 1]);
            }

            // solo decir que fue actualizado
            return ['success' => 1];
        }
        else{
            return ['success' => 99];
        }
    }




}
