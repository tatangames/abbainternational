<?php

namespace App\Http\Controllers\api\Comunidad;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacion;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\ComunidadSolicitud;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\InsigniasTextos;
use App\Models\InsigniasUsuarios;
use App\Models\NivelesInsignias;
use App\Models\NotificacionTextos;
use App\Models\NotificacionUsuario;
use App\Models\OcultarPlanes;
use App\Models\Pais;
use App\Models\Planes;
use App\Models\PlanesAmigos;
use App\Models\PlanesAmigosDetalle;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBlockDetaUsuario;
use App\Models\PlanesBloques;
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use App\Models\TipoInsignias;
use App\Models\TipoNotificacion;
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

                    // necesito saber si el usuario envia (userToken) y el recibe (encontrado)
                    // NO esten ya una fila registrada y con estado 0 o 1

                    if($busquedaEnvia = ComunidadSolicitud::where('id_usuario_envia', $userToken->id)
                        ->where('id_usuario_recibe', $infoEncontrado->id)
                        ->whereIn('estado', [0,1])
                        ->first()){

                        // 0: pendiente de aceptar solicitud
                        $estado = $busquedaEnvia->estado;
                        return ['success' => 1, 'estado' => $estado];
                    }

                    if($busquedaRecibe = ComunidadSolicitud::where('id_usuario_envia', $infoEncontrado->id)
                        ->where('id_usuario_recibe', $userToken->id)
                        ->whereIn('estado', [0,1])
                        ->first()){

                        $estado = $busquedaRecibe->estado;
                        return ['success' => 1, 'estado' => $estado];
                    }

                        // registrar y enviar notificacion en segundo plano

                        $fechaActual = $this->retornoZonaHorariaUsuario($userToken->id_iglesia);

                        $nuevo = new ComunidadSolicitud();
                        $nuevo->id_usuario_envia = $userToken->id;
                        $nuevo->id_usuario_recibe = $infoEncontrado->id;
                        $nuevo->fecha = $fechaActual;
                        $nuevo->estado = 0;
                        $nuevo->save();


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


                    DB::commit();
                    return ['success' => 2,
                        'msg' => "solicitud enviada"];
                }else{
                    return ['success' => 3,
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

                if($userToken->id == $dato->id_usuario_recibe){
                    // no quiero de este
                    $infoUsuario = Usuarios::where('id', $dato->id_usuario_envia)->first();

                    $dato->correo = $infoUsuario->correo;
                    $fecha = date("d-m-Y", strtotime($dato->fecha));
                    $dato->fecha = $fecha;
                }else{
                    $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();

                    $dato->correo = $infoUsuario->correo;
                    $fecha = date("d-m-Y", strtotime($dato->fecha));
                    $dato->fecha = $fecha;
                }


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

                // datos de cual usuario quiero
                if($dato->id_usuario_envia == $userToken->id){
                    $infoUsuario = Usuarios::where('id', $dato->id_usuario_recibe)->first();
                }else{
                    $infoUsuario = Usuarios::where('id', $dato->id_usuario_envia)->first();
                }


                $infoIglesia = Iglesias::where('id', $infoUsuario->id_iglesia)->first();
                $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
                $infoPais = Pais::where('id', $infoDepartamento->id_pais)->first();

                // siempre es requerido apellido
                $nombreFull = $infoUsuario->nombre . " " . $infoUsuario->apellido;

                // este es usuario, se agregara a tabla planes_amigos_detalle
                // ya que son los que daran puntos a usuario en tabla planes_amigos
                $dato->idusuario = $infoUsuario->id;
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
            if($info = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){

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
            'idiomaplan' => 'required',
            'iduser' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {


            $idiomaTextos = $request->idiomaplan;


            if($infoComu = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){


                // datos de cual usuario quiero
                if($infoComu->id_usuario_envia == $userToken->id){
                    $infoUsuario = Usuarios::where('id', $infoComu->id_usuario_recibe)->first();
                }else{
                    $infoUsuario = Usuarios::where('id', $infoComu->id_usuario_envia)->first();
                }


                // ************** BLOQUE INSIGNIAS ******************

                // ordenar por fechas ganadas deberia ser mejor
                // solo visibles

                $insignia_arrayInsignias = DB::table('tipo_insignias AS t')
                    ->join('insignias_usuarios AS i', 'i.id_tipo_insignia', '=', 't.id')
                    ->where('i.id_usuario', $infoUsuario->id)
                    ->select('t.visible', 'i.id_usuario', 'i.id_tipo_insignia', 'i.fecha')
                    ->get();


                $hayInsignias = 0;

                if($insignia_arrayInsignias != null && $insignia_arrayInsignias->isNotEmpty()){
                    $hayInsignias = 1;
                }



                foreach ($insignia_arrayInsignias as $dato){

                    $infoTitulos = $this->retornoTituloInsigniasAppIdioma($dato->id_tipo_insignia, $idiomaTextos);

                    $dato->titulo = $infoTitulos['titulo'];
                    $dato->descripcion = $infoTitulos['descripcion'];


                    // Conocer que nivel voy (ejemplo devuelve 5)
                    $datoHitoNivel = DB::table('insignias_usuarios_detalle AS indeta')
                        ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                        ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                        ->select('nil.nivel', 'nil.id AS idnivelinsignia')
                        ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                        ->max('nil.nivel');


                    if($datoHitoNivel != null){
                        $dato->nivelvoy = $datoHitoNivel;
                    }else{
                        $dato->nivelvoy = 1;
                    }
                }


                $arrayFinalInsignias = $insignia_arrayInsignias->sortBy('titulo')->values();


                return ['success' => 1,
                    'hayinfo' => $hayInsignias,
                    'listado' => $arrayFinalInsignias];

            }else{
                return ['success' => 99, 'msg' => "Solicitud no encontrada"];
            }
        }
        else{
            return ['success' => 99];
        }
    }


    // RETORNO TITULO Y DESCRIPCION DE LAS INSIGNIAS
    private function retornoTituloInsigniasAppIdioma($idInsignia, $idiomaTexto){

        if($infoTexto = InsigniasTextos::where('id_idioma_planes', $idiomaTexto)
            ->where('id_tipo_insignia', $idInsignia)
            ->first()){

            return ['titulo' => $infoTexto->texto_1,
                'descripcion' => $infoTexto->texto_2];

        }else{
            // si no encuentra sera por defecto español

            $infoTexto = InsigniasTextos::where('id_idioma_planes', 1)
                ->where('id_tipo_insignia', $idInsignia)
                ->first();

            return ['titulo' => $infoTexto->texto_1,
                'descripcion' => $infoTexto->texto_2];
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
            $conteoNoti = NotificacionUsuario::where('id_usuario', $userToken->id)->count();

            if ($conteoNoti != null && $conteoNoti > 0) {
                $hayInfo = 1;
            }

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            $arrayNotificacion = NotificacionUsuario::where('id_usuario', $userToken->id)
                ->orderBy('id', 'DESC')
                ->paginate($limit, ['*'], 'page', $page);

            foreach ($arrayNotificacion as $dato){

               $infoTipoNoti = TipoNotificacion::where('id', $dato->id_tipo_notificacion)->first();
               $hayimagen = 0;
               if($infoTipoNoti->imagen != null){
                   $hayimagen = 1;
                   $dato->imagen = $infoTipoNoti->imagen;
               } else{
                   $dato->imagen = null;
               }

               $dato->hayimagen = $hayimagen;
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

            DB::beginTransaction();

            try {


                // PASAR A ESTADO 1
                if($info = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){

                    // datos de cual usuario quiero
                    if($info->id_usuario_envia == $userToken->id){
                        $infoUsuario = Usuarios::where('id', $info->id_usuario_recibe)->first();
                    }else{
                        $infoUsuario = Usuarios::where('id', $info->id_usuario_envia)->first();
                    }


                    $fechaActual = $this->retornoZonaHorariaUsuario($infoUsuario->id_iglesia);

                    // registrar un historial
                    $notiHistorial = new NotificacionUsuario();
                    $notiHistorial->id_usuario = $infoUsuario->id;
                    $notiHistorial->id_tipo_notificacion = 12; // solicitud aceptada
                    $notiHistorial->fecha = $fechaActual;
                    $notiHistorial->save();

                    $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $infoUsuario->id)->get();
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
                        $datosRaw = $this->retornoTitulosNotificaciones(12, $infoUsuario->idioma_noti);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];

                        // como es primera vez, se necesita enviar notificacion
                        dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                    }

                    ComunidadSolicitud::where('id', $info->id)->update(['estado' => 1]);
                }else{

                    // SOLICITUD YA NO EXISTE

                    return ['success' => 2];


                }

                DB::commit();
                return ['success' => 1];

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


    public function iniciarPlanConAmigos(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idplan' => 'required',
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

            DB::beginTransaction();

            try {

                // TAMBIEN VERIFICAR QUE PLAN NO ESTE INICIADO YA
                if(PlanesUsuarios::where('id_usuario', $userToken->id)
                    ->where('id_planes', $request->idplan)->first()){

                    return ['success' => 1, 'msg' => 'plan ya estaba registrado'];
                }

                $zonaHoraria = $this->retornoZonaHorariaUsuario($userToken->id_iglesia);

                // registrar plan a usuario
                $nuevoPlan = new PlanesUsuarios();
                $nuevoPlan->id_usuario = $userToken->id;
                $nuevoPlan->id_planes = $request->idplan;
                $nuevoPlan->fecha = $zonaHoraria;
                $nuevoPlan->save();

                if ($request->has('idsolicitud')) {

                    foreach ($request->idsolicitud as $clave => $valor) {

                        $idusuarioVal = $valor['idusuario'];

                        // Registrar
                        $detalle = new PlanesAmigosDetalle();
                        $detalle->id_planes_usuarios = $nuevoPlan->id;
                        $detalle->id_comunidad_solicitud = $clave;
                        $detalle->id_usuario = $idusuarioVal;
                        $detalle->save();



                        // NOTIFICACION A USUARIOS QUE FUE UNIDO A UN PLAN GRUPAL
                        // GUARDARLE HISTORIAL
                        $notiHistorial = new NotificacionUsuario();
                        $notiHistorial->id_usuario = $idusuarioVal;
                        $notiHistorial->id_tipo_notificacion = 13;
                        $notiHistorial->fecha = $zonaHoraria;
                        $notiHistorial->save();



                        $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $idusuarioVal)->get();
                        $pilaOneSignal = array();
                        $hayIdOne = false;
                        foreach ($arrayOneSignal as $item){
                            if($item->onesignal != null){
                                $hayIdOne = true;
                                array_push($pilaOneSignal, $item->onesignal);
                            }
                        }

                        if($hayIdOne){

                            $infoUsuario = Usuarios::where('id', $idusuarioVal)->first();

                            // UN AMIGO TE ACABA DE ENVIAR UNA SOLICITUD
                            $datosRaw = $this->retornoTitulosNotificaciones(12, $infoUsuario->idioma_noti);
                            $tiNo = $datosRaw['titulo'];
                            $desNo = $datosRaw['descripcion'];

                            // como es primera vez, se necesita enviar notificacion
                            dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                        }


                    }
                }

                DB::commit();
                return ['success' => 2];

            }catch(\Throwable $e){
                Log::info("error: " . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }






    public function iniciarPlanConAmigosIphone(Request $request){

        Log::info($request->all());


        $rules = array(
            'iduser' => 'required',
            'idplan' => 'required',
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

            DB::beginTransaction();

            try {

                // TAMBIEN VERIFICAR QUE PLAN NO ESTE INICIADO YA
                if(PlanesUsuarios::where('id_usuario', $userToken->id)
                    ->where('id_planes', $request->idplan)->first()){

                    return ['success' => 1, 'msg' => 'plan ya estaba registrado'];
                }

                $zonaHoraria = $this->retornoZonaHorariaUsuario($userToken->id_iglesia);

                // registrar plan a usuario
                $nuevoPlan = new PlanesUsuarios();
                $nuevoPlan->id_usuario = $userToken->id;
                $nuevoPlan->id_planes = $request->idplan;
                $nuevoPlan->fecha = $zonaHoraria;
                $nuevoPlan->save();






                if ($request->has('datos')) {

                    foreach ($request->datos as $dato) {

                        $clave = $dato['id']; // ID SOLICITUD
                        $idusuarioVal = $dato['estado']; // IDUSUARIO

                            // Registrar
                            $detalle = new PlanesAmigosDetalle();
                            $detalle->id_planes_usuarios = $nuevoPlan->id;
                            $detalle->id_comunidad_solicitud = $clave;
                            $detalle->id_usuario = $idusuarioVal;
                            $detalle->save();



                            // NOTIFICACION A USUARIOS QUE FUE UNIDO A UN PLAN GRUPAL
                            // GUARDARLE HISTORIAL
                            $notiHistorial = new NotificacionUsuario();
                            $notiHistorial->id_usuario = $idusuarioVal;
                            $notiHistorial->id_tipo_notificacion = 13;
                            $notiHistorial->fecha = $zonaHoraria;
                            $notiHistorial->save();



                            $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $idusuarioVal)->get();
                            $pilaOneSignal = array();
                            $hayIdOne = false;
                            foreach ($arrayOneSignal as $item){
                                if($item->onesignal != null){
                                    $hayIdOne = true;
                                    array_push($pilaOneSignal, $item->onesignal);
                                }
                            }

                            if($hayIdOne){

                                $infoUsuario = Usuarios::where('id', $idusuarioVal)->first();

                                // UN AMIGO TE ACABA DE ENVIAR UNA SOLICITUD
                                $datosRaw = $this->retornoTitulosNotificaciones(12, $infoUsuario->idioma_noti);
                                $tiNo = $datosRaw['titulo'];
                                $desNo = $datosRaw['descripcion'];

                                // como es primera vez, se necesita enviar notificacion
                                dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                            }



                    }
                }



                DB::commit();
                return ['success' => 2];

            }catch(\Throwable $e){
                Log::info("error: " . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }












    public function informacionPlanesAmigo(Request $request){

        $rules = array(
            'idsolicitud' => 'required',
            'idiomaplan' => 'required',
            'iduser' => 'required'
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $request->idiomaplan;


            if($infoComu = ComunidadSolicitud::where('id', $request->idsolicitud)->first()){


                // datos de cual usuario quiero
                if($infoComu->id_usuario_envia == $userToken->id){
                    $infoUsuario = Usuarios::where('id', $infoComu->id_usuario_recibe)->first();
                }else{
                    $infoUsuario = Usuarios::where('id', $infoComu->id_usuario_envia)->first();
                }




                // ************** BLOQUE PLANES ******************


                // Obtener todos los id que estan ocultos por el usuario
                $noPlanes = OcultarPlanes::where('id_usuario', $infoUsuario->id)
                    ->select('id_planes')
                    ->where('estado', 1) // los ocultos
                    ->get();

                $arrayPlanes = PlanesUsuarios::where('id_usuario', $infoUsuario->id)
                    ->whereNotIn('id_planes', $noPlanes)
                    ->get();

                $hayinfo = 0;

                foreach ($arrayPlanes as $dato){
                    $hayinfo = 1;

                    $infoPlan = Planes::where('id', $dato->id_planes)->first();
                    $dato->imagen = $infoPlan->imagen;

                    $datosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);
                    $dato->titulo = $datosRaw['titulo'];
                }

                $arrayPlanesOrdenado = $arrayPlanes->sortBy('titulo')->values();


                return ['success' => 1,
                    'usuariobuscado' => $infoUsuario->id, // con este usuario buscar items y preguntas
                    'hayinfo' => $hayinfo,
                    'listadoplan' => $arrayPlanesOrdenado];

            }else{
                return ['success' => 99, 'msg' => "Solicitud no encontrada"];
            }
        }
        else{
            return ['success' => 99];
        }
    }


    private function retornoTituloPlan($idiomaplan, $idplan){

        // si encuentra idioma solicitado
        if($infoPlanTexto = PlanesTextos::where('id_planes', $idplan)
            ->where('id_idioma_planes', $idiomaplan)
            ->first()){

            return ['titulo' => $infoPlanTexto->titulo,
                'subtitulo' => $infoPlanTexto->subtitulo
            ];

        }else{
            // si no encuentra sera por defecto español

            $infoPlanTexto = PlanesTextos::where('id_planes', $idplan)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['titulo' => $infoPlanTexto->titulo,
                'subtitulo' => $infoPlanTexto->subtitulo
            ];
        }
    }



    public function informacionPlanesAmigoItems(Request $request){

        $rules = array(
            'idplan' => 'required',
            'idiomaplan' => 'required',
            'idusuariobuscar' => 'required', // con este usuario se busca los items
            'iduser' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $request->idiomaplan;

            // OBTENER TODOS LOS ITEMS



            $arrayItemsLista = DB::table('planes AS p')
                ->join('planes_bloques AS pb', 'pb.id_planes', '=', 'p.id')
                ->join('planes_block_detalle AS pbd', 'pbd.id_planes_bloques', '=', 'pb.id')
                ->select('pbd.id')
                ->whereIn('p.id', [$request->idplan])
                ->get();

            $pilaItems = array();

            foreach ($arrayItemsLista as $item){
                array_push($pilaItems, $item->id);
            }

            $arrayItems = PlanesBlockDetaUsuario::where('id_usuario', $request->idusuariobuscar)
                ->whereIn('id_planes_block_deta', $pilaItems)
                ->where('completado', 1)
                ->get();

            $hayinfo = 0;
            foreach ($arrayItems as $dato){
                $hayinfo = 1;

                $titulo = $this->retornoTituloBloquesTextos($idiomaTextos, $dato->id_planes_block_deta);
                $dato->titulo = $titulo;

                $infoPlanesBlockDeta = PlanesBlockDetalle::where('id', $dato->id_planes_block_deta)->first();
                $infoPlanesBloques = PlanesBloques::where('id', $infoPlanesBlockDeta->id_planes_bloques)->first();

                $dato->fecha = date("d-m-Y", strtotime($infoPlanesBloques->fecha_inicio));
            }

            $arrayItemsOrdenados = $arrayItems->sortBy('fecha')->values();

            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listadoplan' => $arrayItemsOrdenados];
        }
        else{
            return ['success' => 99];
        }
    }


    // RETORNA TITULO DEL BLOQUE DETALLE TEXTOS
    private function retornoTituloBloquesTextos($idiomaTextos, $idBlockDetalle){

        if($infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDetalle)
            ->where('id_idioma_planes', $idiomaTextos)
            ->first()){

            return $infoTituloTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDetalle)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoTituloTexto->titulo;
        }
    }



    public function informacionPlanesAmigoItemsPreguntas(Request $request){

        $rules = array(
            'idplanblockdetauser' => 'required',
            'idiomaplan' => 'required',
            'idusuariobuscar' => 'required'
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if($info = PlanesBlockDetaUsuario::where('id', $request->idplanblockdetauser)->first()){
                $idiomaTexto = $request->idiomaplan;

                $idplanblockdeta = $info->id_planes_block_deta;

                $listadoPreguntas = BloquePreguntas::where('id_plan_block_detalle', $idplanblockdeta)
                    ->orderBy('posicion', 'ASC')
                    ->get();

                $hayinfo = 0;

                foreach ($listadoPreguntas as $dato){
                    $hayinfo = 1;

                    $titulo = $this->retornoTituloPreguntaTextoIdioma($dato->id, $idiomaTexto);
                    $dato->titulo = $titulo;

                    $respuesta = "";

                    // hoy buscar la respuesta, de ese usuario para esta pregunta
                    if($infoPre = BloquePreguntasUsuarios::where('id_bloque_preguntas', $dato->id)
                        ->where('id_usuarios', $request->idusuariobuscar)->first()){

                        $respuesta = $infoPre->texto;
                    }

                    $dato->respuesta = $respuesta;
                }

                return ['success' => 1,
                    'hayinfo' => $hayinfo,
                    'listado' => $listadoPreguntas];
            }else{
                return ['success' => 99];
            }
        }
        else{
            return ['success' => 99];
        }


    }





    // RETORNO DE TITULO DE PREGUNTA SEGUN IDIOMA
    private function retornoTituloPreguntaTextoIdioma($idPregunta, $idiomaTexto){

        if($infoTituloTexto = BloquePreguntasTextos::where('id_bloque_preguntas', $idPregunta)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return $infoTituloTexto->texto;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = BloquePreguntasTextos::where('id_bloque_preguntas', $idPregunta)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoTituloTexto->texto;
        }

    }


    // DEVUELTE TEXTO CON IDIOMA DE LO ESCRITOP

    public function infoPlanesUsuarios(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required'
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
            $idiomaTextos = $request->idiomaplan;





            $arrayPlanes = DB::table('planes AS p')
                ->join('planes_usuarios AS pu', 'pu.id_planes', '=', 'p.id')
                ->select('p.visible', 'pu.id_usuario', 'pu.id_planes', 'pu.fecha', 'p.visible')
                ->where('p.visible', 1)
                ->where('pu.id_usuario', $userToken->id)
                ->get();




            foreach ($arrayPlanes as $dato){
                $hayinfo = 1;

                $infoPlan = Planes::where('id', $dato->id_planes)->first();
                $dato->imagen = $infoPlan->imagen;

                $datosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);
                $dato->titulo = $datosRaw['titulo'];

                $marcado = false;

                if($infoOc = OcultarPlanes::where('id_usuario', $userToken->id)
                    ->where('id_planes', $dato->id_planes)->first()){
                    if($infoOc->estado == 1){
                        $marcado = true;
                    }
                }
                $dato->estado = $marcado;
            }

            $arrayPlanesOrdenado = $arrayPlanes->sortBy('titulo')->values();

            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayPlanesOrdenado];
        }
        else{
            return ['success' => 99];
        }
    }


    public function actualizarPlanesOcultos(Request $request)
    {

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

            DB::beginTransaction();

            try {

                OcultarPlanes::where('id_usuario', $userToken->id)
                    ->update(['estado' => 0]);

                if ($request->has('idplan')) {

                    foreach ($request->idplan as $clave => $valor) {

                        $idestado = $valor['estado'];

                        // si existe solo actualizar
                        if($fila = OcultarPlanes::where('id_usuario', $userToken->id)
                            ->where('id_planes', $clave)->first()){

                            OcultarPlanes::where('id', $fila->id)
                                ->update(['estado' => $idestado]);

                        }else{

                            // crear
                            $nuevo = new OcultarPlanes();
                            $nuevo->id_usuario = $userToken->id;
                            $nuevo->id_planes = $clave;
                            $nuevo->estado = $valor['estado'];
                            $nuevo->save();
                        }
                    }
                }


                DB::commit();
                return ['success' => 1];

            }catch(\Throwable $e){
                Log::info("error: " . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }


    public function actualizarPlanesOcultosIphone(Request $request)
    {
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

            DB::beginTransaction();

            try {

                OcultarPlanes::where('id_usuario', $userToken->id)
                    ->update(['estado' => 0]);

                if ($request->has('datos')) {

                    foreach ($request->datos as $dato) {

                        $clave = $dato['id'];
                        $idestado = $dato['estado'];

                        // si existe solo actualizar
                        if($fila = OcultarPlanes::where('id_usuario', $userToken->id)
                            ->where('id_planes', $clave)->first()){

                            OcultarPlanes::where('id', $fila->id)
                                ->update(['estado' => $idestado]);

                        }else{

                            // crear
                            $nuevo = new OcultarPlanes();
                            $nuevo->id_usuario = $userToken->id;
                            $nuevo->id_planes = $clave;
                            $nuevo->estado = $idestado;
                            $nuevo->save();
                        }
                    }
                }


                DB::commit();
                return ['success' => 1];

            }catch(\Throwable $e){
                Log::info("error: " . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }



}
