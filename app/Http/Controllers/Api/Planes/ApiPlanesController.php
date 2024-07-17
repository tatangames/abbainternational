<?php

namespace App\Http\Controllers\Api\Planes;

use App\FuentesCssLetra;
use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacion;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\ComunidadSolicitud;
use App\Models\Departamentos;
use App\Models\DevocionalBiblia;
use App\Models\DevocionalCapitulo;
use App\Models\Iglesias;
use App\Models\ImagenPreguntas;
use App\Models\InsigniasUsuarios;
use App\Models\InsigniasUsuariosConteo;
use App\Models\InsigniasUsuariosDetalle;
use App\Models\NivelesInsignias;
use App\Models\NotificacionTextos;
use App\Models\NotificacionUsuario;
use App\Models\Planes;
use App\Models\PlanesAmigosDetalle;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBlockDetaUsuario;
use App\Models\PlanesBlockDetaUsuarioTotal;
use App\Models\PlanesBloques;
use App\Models\PlanesBloquesTextos;
use App\Models\PlanesContenedor;
use App\Models\PlanesContenedorTextos;
use App\Models\PlanesFinalizadosUsuario;
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use App\Models\PlanesUsuariosContinuar;
use App\Models\RachaDevocional;
use App\Models\UsuarioNotificaciones;
use App\Models\Usuarios;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use DateTime;
use Symfony\Component\DomCrawler\Crawler;





class ApiPlanesController extends Controller
{

    // retorna planes que no ha agregado el usuario
    public function buscarPlanesNoAgregados(Request $request){

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


            // todos los planes de mi usuario
            $arrayIdYaSeleccionados = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            // conocer si habra planes disponibles
            $hayInfo = 0;

            // obtener todos los planes NO elegido por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)->get();


            if ($arrayPlanes->isNotEmpty()) {
                $hayInfo = 1;
            }


            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);


            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)
                ->paginate($limit, ['*'], 'page', $page);

            foreach ($arrayPlanes as $dato){
                $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id);
                $dato->titulo = $arrayRaw['titulo'];
                $dato->subtitulo = $arrayRaw['subtitulo'];
            }

            // sortByDesc
            $sortedResult = $arrayPlanes->getCollection()->sortBy('posicion')->values();
            $arrayPlanes->setCollection($sortedResult);


            return [
                'success' => 1,
                'hayinfo' => $hayInfo,
                'listado' => $arrayPlanes
                ];
        }else{
            return ['success' => 99];
        }
    }


    // RETORNO DE NOMBRE, SUBTITULO DEL PLAN SEGUN IDIOMA
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


    // ver informacion de un plan para poder seleccionarlo
    public function informacionPlanSeleccionado(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'idplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        if($infoPlan = Planes::where('id', $request->idplan)->first()){

            $idiomaTextos = $request->idiomaplan;

            $titulo = "";
            $subtitulo = null;
            $descripcion = null;

            if($infoPlanTextos = PlanesTextos::where('id_planes', $request->idplan)
                ->where('id_idioma_planes', $idiomaTextos)
                ->first()){
                $titulo = $infoPlanTextos->titulo;
                $subtitulo = $infoPlanTextos->subtitulo;
                $descripcion = $infoPlanTextos->descripcion;
            }

            return ['success' => 1,
                'imagen' => $infoPlan->imagenportada,
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'descripcion' => $descripcion
                ];
        }else{
            return ['success' => 99];
        }
    }





    // selecciona un plan para iniciarlo
    public function iniciarPlanNuevo(Request $request){
        $rules = array(
            'idplan' => 'required',
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if(PlanesUsuarios::where('id_usuario', $userToken->id)
                ->where('id_planes', $request->idplan)->first()){

                // ya estaba agregado
                return ['success' => 1,
                    'msg' => 'plan ya estaba registrado'];
            }else{
                DB::beginTransaction();

                try {

                    $zonaHoraria = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);

                    $nuevoPlan = new PlanesUsuarios();
                    $nuevoPlan->id_usuario = $userToken->id;
                    $nuevoPlan->id_planes = $request->idplan;
                    $nuevoPlan->fecha = $zonaHoraria;
                    $nuevoPlan->save();

                    DB::commit();
                    return ['success' => 2];

                }catch(\Throwable $e){
                    Log::info("error: " . $e);
                    DB::rollback();
                    return ['success' => 99];
                }
            }
        }else{
            return ['success' => 99];
        }
    }


    // devuelve mis planes que he seleccionado, habra algunos que pasaran a 'completados' pero
    // se verificaran dinamicamente
    public function listadoMisPlanes(Request $request)
    {
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

        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {


            $arrayPlanUsuario = DB::table('planes AS p')
                ->join('planes_usuarios AS pu', 'pu.id_planes', '=', 'p.id')
                ->select('pu.id_usuario', 'pu.id_planes')
                ->where('pu.id_usuario', $userToken->id)
                ->get();


            foreach ($arrayPlanUsuario as $dato){

                $arrayPlanBloque = PlanesBloques::where('id_planes', $dato->id_planes)->get();

                if($arrayPlanBloque != null && $arrayPlanBloque->isNotEmpty()){
                    foreach ($arrayPlanBloque as $rec){ // cada bloque

                        // buscar el detalle de ese bloque
                        $arrayListado = PlanesBlockDetalle::where('id_planes_bloques', $rec->id)
                            ->get();

                        foreach ($arrayListado as $datoLista){

                            if($detauser = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                                ->where('id_planes_block_deta', $datoLista->id)
                                ->first()){
                                // si encontro, verificar si esta completo
                                if($detauser->completado == 0){
                                    $planCompletado = 0;
                                    break;
                                }
                            }else{
                                // no encontrado, asi que retornar
                                $planCompletado = 0;
                                break;
                            }
                        }
                    }
                }
            }


            // obtener los planes que no esten completados
            $pilaIdPlanNoComplet = array();

            foreach ($arrayPlanUsuario as $item){
                if($item->plancompletado == 0){
                    array_push($pilaIdPlanNoComplet, $item->id_planes);
                }
            }



            // ***** AQUI INICIA LA PAGINACION *****

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);


            $arrayPlanesUser = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->whereIn('id_planes', $pilaIdPlanNoComplet)
                ->paginate($limit, ['*'], 'page', $page);

            foreach ($arrayPlanesUser as $dato){
                $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                $dato->titulo = $titulosRaw['titulo'];
                $dato->subtitulo = $titulosRaw['subtitulo'];

                $infoP = Planes::where('id', $dato->id_planes)->first();
                $dato->imagen = $infoP->imagen;
                $dato->imagenportada = $infoP->imagenportada;
                $dato->idplan = $infoP->id;
            }

            // sortByDesc
            $sortedResult = $arrayPlanesUser->getCollection()->sortBy('id')->values();
            $arrayPlanesUser->setCollection($sortedResult);

            $hayinfo = 0;
            // EN LA APP: se verifica la primera vez con un boolean
            if ($arrayPlanesUser->count() > 0) {
                $hayinfo = 1;
            }


            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayPlanesUser,
            ];

        }else{
            return ['success' => 99];
        }
    }


    // INFORMACION DE BLOQUE FECHAS

    // devuelve informacion del plan a continuar, todos el bloque
    public function informacionBloqueMiPlan(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {


            $infoPlan = Planes::where('id', $request->idplan)->first();

            // obtener todos los bloques ordenados por fecha
            $arrayBloques = PlanesBloques::where('id_planes', $request->idplan)
                ->orderBy('fecha_inicio', 'ASC')
                ->get();


            $resultsBloque = array();
            $index = 0;


            foreach ($arrayBloques as $dato){
                array_push($resultsBloque, $dato);


                // TEXTO PERSONALIZADO EN EL BLOQUE


                $textoPersonalizado = $this->retornoTextoPersonalizadoPlan($idiomaTextos, $dato->id);
                $dato->textopersonalizado = $textoPersonalizado;

                // agregar detalle bloques
                $arrayDetaBloque = PlanesBlockDetalle::where('id_planes_bloques', $dato->id)
                    ->orderBy('posicion', 'ASC')
                    ->get();



                foreach ($arrayDetaBloque as $datoArr){

                    $datoArr->titulo = $this->retornoTituloBloquesTextos($idiomaTextos, $datoArr->id);

                    // SE GUARDO PREGUNTAS
                    $estaCompletado = 0;


                    // saber si esta check para mi usuario
                    if(PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes_block_deta', $datoArr->id)
                    ->first()){
                        $estaCompletado = 1;
                    }

                    $datoArr->completado = $estaCompletado;
                }

                $resultsBloque[$index]->detalle = $arrayDetaBloque;
                $index++;
            }



            if($idiomaTextos == 1){
                $imgPortada = $infoPlan->imagenportada;
            }else{
                $imgPortada = $infoPlan->imagenportada_ingles;
            }


            return ['success' => 1,
                'portada' => $imgPortada,
                'listado' => $arrayBloques,
                ];
        }else{
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



    // RETORNA 3 PRIMERAS LETRAS PARA LOS BLOQUES DE LOS PLANES
    private function retorno3LetrasFechasIdioma($idiomaTextos, $fecha){

        $dateTime = new DateTime($fecha);

        if($idiomaTextos == 1){ // espanol

            $intlDateFormatter = new \IntlDateFormatter('es_ES', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }
        else if($idiomaTextos == 2){ // ingles
            $intlDateFormatter = new \IntlDateFormatter('en_US', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }else{
            // defecto: espanol
            $intlDateFormatter = new \IntlDateFormatter('es_ES', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }

        return $intlDateFormatter->format($dateTime);
    }


    // actualizar el check de cada plan
    public function actualizarCheckBloqueMiPlan(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
            'valor' => 'required',
            'idplan' => 'required',
            'idiomaplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            DB::beginTransaction();

            try {


                // ESTO ES TESTEO LOCAL, EN PRODUCCION DEBE ESTAR EN TRUE
                $permitirNotificacion = true;
                $idiomaTexto = $request->idiomaplan;
                $fechaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);


                // EVITAR UN PLAN BORRADO
                if(!Planes::where('id', $request->idplan)->first()){
                    return ['success' => 1, 'msg' => "el plan no existe"];
                }


                // ****************** BLOQUE 1 *********************

                $arrayConteoPreguntas = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)->count();

                // NO EXISTEN PREGUNTAS REGISTRADAS POR ADMINISTRADOR
                if($arrayConteoPreguntas <= 0){
                    return ['success' => 1, 'msg' => "no hay ninguna pregunta"];
                }


                // TODAS LAS PREGUNTAS REGISTRADAS POR ADMINISTRADOR
                $arrayPreguntas = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)->get();



                $hayRespondidas = false;

                // CON SOLO UNA PREGUNTA RESPONDIDA POR EL USUARIO ES SUFICIENTE
                foreach ($arrayPreguntas as $dato){
                    if(BloquePreguntasUsuarios::where('id_bloque_preguntas', $dato->id)
                        ->where('id_usuarios', $userToken->id)->first()){
                          $hayRespondidas = true;
                          break;
                    }
                }

                // SI ES FALSO, TAMBIEN NO DEJARA
                if(!$hayRespondidas){
                    return ['success' => 1, 'msg' => "falta responder preguntas"];
                }





                //*******************************



                // SOLO SUMAR PUNTO SI ES PRIMERA VEZ GUARDANDO ITEM
                $primeraVezItem = false;

                // si existe, solo actualizar
                if(PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes_block_deta', $request->idblockdeta)
                    ->first()){

                    // SI ENTRA AQUI, DIGAMOS QUE EL CHECK VINO DE NUEVO,
                    // SOLO DECIRLE AL USUARIO ACTUALIZADO, Y AL CARGAR DE NUEVO LA PANTALLA
                    // YA NO SALDRA EL CHECK
                    return ['success' => 2, 'msg' => 'actualizado'];

                }else{

                    // PRIMERA VEZ QUE LLEGA EL CHECK
                    // ESTO HARA QUE NO VUELVA APARECE EL CHECK DE NUEVO
                    $detalle = new PlanesBlockDetaUsuario();
                    $detalle->id_usuario = $userToken->id;
                    $detalle->id_planes_block_deta = $request->idblockdeta;
                    $detalle->fecha = $fechaCarbon;
                    $detalle->completado = 1;
                    $detalle->save();


                    // PARA CONTAR LOS DIAS SEGUIDOS, MAS DESCRIPCION EN TABLA MIGRATE
                    $regDias = new PlanesBlockDetaUsuarioTotal();
                    $regDias->id_usuario = $userToken->id;
                    $regDias->id_planes_block_deta = $request->idblockdeta;
                    $regDias->fecha = $fechaCarbon;
                    $regDias->save();

                    $primeraVezItem = true;
                }



                ///*********************** PARTE DE LAS INSIGNIAS **********************************


                // INSIGNIAS
                // Rachas / Días lectura y devocional
                $idTipoInsignia = 4; // RACHA DEVOCIONAL


                // OBTENER LOS IDENTIFICADORES DE ONE SIGNAL
                $infoUsuarioFila = Usuarios::where('id', $userToken->id)->first();
                $idOneSignalUsuario = $infoUsuarioFila->onesignal;

                $hayIdOne = false;

                if($idOneSignalUsuario != null){
                    if(strlen($idOneSignalUsuario) == 0){
                        // vacio no hacer nada
                    }else{
                        $hayIdOne = true;
                    }
                }









                // Rachas / Días lectura y devocional
                if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsignia)
                    ->where('id_usuario', $userToken->id)->first()){

                    // AQUI SE SUMA CONTADOR Y SE VERIFICA SI GANARA EL HITO


                    // SOLO SUMAR PUNTO SI ES PRIMERA VEZ GUARDANDO ITEM
                    if($primeraVezItem){

                        $infoConteo = InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsignia)
                            ->where('id_usuarios', $userToken->id)
                            ->first();

                        $conteo = $infoConteo->conteo;
                        $conteo++;

                        $arrayNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)
                            ->orderBy('nivel', 'ASC')
                            ->get();

                        $enviarNoti = false;



                        // verificar si ya alcanzo nivel
                        foreach ($arrayNiveles as $dato){

                            if($conteo >= $dato->nivel){
                                // pero verificar que no este el hito registrado
                                if(InsigniasUsuariosDetalle::where('id_niveles_insignias', $dato->id)
                                    ->where('id_usuarios', $userToken->id)
                                    ->first()){
                                    // no hacer nada porque ya esta el hito, se debe seguir el siguiente nivel

                                }else{
                                    $enviarNoti = true;

                                    // registrar hito - nivel y salir bucle
                                    $nuevoDeta = new InsigniasUsuariosDetalle();
                                    $nuevoDeta->id_niveles_insignias = $dato->id;
                                    $nuevoDeta->id_usuarios = $userToken->id;
                                    $nuevoDeta->fecha = $fechaCarbon;
                                    $nuevoDeta->save();


                                    // SUBIO NIVEL HITO - INSIGNIA COMPARTIR DEVOCIONAL
                                    $notiHistorial = new NotificacionUsuario();
                                    $notiHistorial->id_usuario = $userToken->id;
                                    $notiHistorial->id_tipo_notificacion = 4;
                                    $notiHistorial->fecha = $fechaCarbon;
                                    $notiHistorial->save();

                                    break;
                                }
                            }
                        }

                        if($enviarNoti){

                            if($hayIdOne){

                                // 8: Subiste de Nivel tu insignia Racha Lectura
                                $datosRaw = $this->retornoTitulosNotificaciones(8, $idiomaTexto);
                                $tiNo = $datosRaw['titulo'];
                                $desNo = $datosRaw['descripcion'];


                                // como es primera vez, se necesita enviar notificacion
                                if($permitirNotificacion) {
                                    dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                                }
                            }
                        }

                        // maximo nivel
                        $maxNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)->max('nivel');

                        if($conteo <= $maxNiveles){

                            // SOLO ACTUALIZAR CONTEO, PORQUE AUN NO ALCANZA EL MAX NIVEL
                            InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsignia)
                                ->where('id_usuarios', $userToken->id)
                                ->update(['conteo' => $conteo]);

                        }
                    }
                }else{

                    // POR PRIMERA VEZ GANANDO LA INSIGNIA RACHA DEVOCIONAL , conteo 1

                    $nuevaInsignia = new InsigniasUsuarios();
                    $nuevaInsignia->id_tipo_insignia = $idTipoInsignia;
                    $nuevaInsignia->id_usuario = $userToken->id;
                    $nuevaInsignia->fecha = $fechaCarbon;
                    $nuevaInsignia->save();


                    $nuevoConteo = new InsigniasUsuariosConteo();
                    $nuevoConteo->id_tipo_insignia = $idTipoInsignia;
                    $nuevoConteo->id_usuarios = $userToken->id;
                    $nuevoConteo->conteo = 1;
                    $nuevoConteo->save();

                    // SIEMPRE EXISTE NIVEL 1
                    $infoIdNivel = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)
                        ->where('nivel', 1)
                        ->first();

                    // hito - por defecto nivel 1
                    $nuevoHito = new InsigniasUsuariosDetalle();
                    $nuevoHito->id_niveles_insignias = $infoIdNivel->id;
                    $nuevoHito->id_usuarios = $userToken->id;
                    $nuevoHito->fecha = $fechaCarbon;
                    $nuevoHito->save();


                    // PRIMERA VEZ - INSIGNIA COMPARTIR DEVOCIONAL
                    $notiHistorial = new NotificacionUsuario();
                    $notiHistorial->id_usuario = $userToken->id;
                    $notiHistorial->id_tipo_notificacion = 3;
                    $notiHistorial->fecha = $fechaCarbon;
                    $notiHistorial->save();


                    if($hayIdOne){
                        // 7: Ganaste Insignia Racha Lectura
                        $datosRaw = $this->retornoTitulosNotificaciones(7, $idiomaTexto);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];

                        // como es primera vez, se necesita enviar notificacion
                        if($permitirNotificacion) {
                            dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                        }
                    }
                }





                $planCompletado = 1;





                // VERIFICA SI TODAS LAS CASILLAS DE TODOS LOS BLOQUES ESTAN COMPLETOS
                $arrayPlanBloque = PlanesBloques::where('id_planes', $request->idplan)->get();

                foreach ($arrayPlanBloque as $dato){

                    // buscar el detalle de cada bloque
                    $arrayListado = PlanesBlockDetalle::where('id_planes_bloques', $dato->id)->get();

                    foreach ($arrayListado as $datoLista){

                        if($detauser = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                            ->where('id_planes_block_deta', $datoLista->id)
                            ->first()){
                            // si encontro, verificar si esta completo
                            if($detauser->completado == 0){
                                $planCompletado = 0;
                                break;
                            }
                        }else{
                            // no encontrado, asi que retornar
                            $planCompletado = 0;
                            break;
                        }
                    }
                }


                $infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first();
                $infoPlanesBloques = PlanesBloques::where('id', $infoBlockDeta->id_planes_bloques)->first();



















                // ******************* BLOQUE 2 *******************************

                if($planCompletado){

                    $idTipoInsigniaBloque2 = 3; // PLANES FINALIZADOS

                    if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsigniaBloque2)
                        ->where('id_usuario', $userToken->id)->first()){



                        // SE DEBE EVITAR QUE SE AUMENTE PUNTO POR EL MISMO PLAN
                        if(PlanesFinalizadosUsuario::where('id_usuario', $userToken->id)
                            ->where('id_planes', $infoPlanesBloques->id_planes)->first()){
                            // no registrar nada
                        }else{

                            // es nuevo plan finalizado, aumentar punto
                            // AQUI SE SUMA CONTADOR Y SE VERIFICA SI GANARA EL HITO


                            // REGISTRAR QUE FINALIZO PLAN USUARIO
                            $finalizado = new PlanesFinalizadosUsuario();
                            $finalizado->id_planes = $infoPlanesBloques->id_planes;
                            $finalizado->id_usuario = $userToken->id;
                            $finalizado->save();


                            $infoConteo = InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsigniaBloque2)
                                ->where('id_usuarios', $userToken->id)
                                ->first();

                            $conteo = $infoConteo->conteo;
                            $conteo++;

                            $arrayNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque2)
                                ->orderBy('nivel', 'ASC')
                                ->get();

                            $enviarNoti = false;



                            // verificar si ya alcanzo nivel
                            foreach ($arrayNiveles as $dato){

                                if($conteo >= $dato->nivel){
                                    // pero verificar que no este el hito registrado
                                    if(InsigniasUsuariosDetalle::where('id_niveles_insignias', $dato->id)
                                        ->where('id_usuarios', $userToken->id)
                                        ->first()){
                                        // no hacer nada porque ya esta el hito, se debe seguir el siguiente nivel

                                    }else{
                                        $enviarNoti = true;

                                        // registrar hito - nivel y salir bucle
                                        $nuevoDeta = new InsigniasUsuariosDetalle();
                                        $nuevoDeta->id_niveles_insignias = $dato->id;
                                        $nuevoDeta->id_usuarios = $userToken->id;
                                        $nuevoDeta->fecha = $fechaCarbon;
                                        $nuevoDeta->save();

                                        // SUBIO NIVEL HITO - PLAN FINALIZADO
                                        $notiHistorial = new NotificacionUsuario();
                                        $notiHistorial->id_usuario = $userToken->id;
                                        $notiHistorial->id_tipo_notificacion = 6;
                                        $notiHistorial->fecha = $fechaCarbon;
                                        $notiHistorial->save();

                                        break;
                                    }
                                }
                            }

                            if($enviarNoti){

                                if($hayIdOne){
                                    // SUBI DE NIVEL INSIGNIA PLANES FINALIZADOS
                                    $datosRaw = $this->retornoTitulosNotificaciones(6, $idiomaTexto);
                                    $tiNo = $datosRaw['titulo'];
                                    $desNo = $datosRaw['descripcion'];

                                    if($permitirNotificacion) {
                                        dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                                    }

                                }
                            }

                            // maximo nivel
                            $maxNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque2)->max('nivel');

                            if($conteo <= $maxNiveles){

                                // solo actualizar conteo

                                InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsigniaBloque2)
                                    ->where('id_usuarios', $userToken->id)
                                    ->update(['conteo' => $conteo]);
                            }


                        }
                    }else{

                        // PRIMERA VEZ GANANDO INSIGNIA PLAN FINALIZADO

                        $nuevaInsignia = new InsigniasUsuarios();
                        $nuevaInsignia->id_tipo_insignia = $idTipoInsigniaBloque2; // compartir App
                        $nuevaInsignia->id_usuario = $userToken->id;
                        $nuevaInsignia->fecha = $fechaCarbon;
                        $nuevaInsignia->save();

                        $nuevoConteo = new InsigniasUsuariosConteo();
                        $nuevoConteo->id_tipo_insignia = $idTipoInsigniaBloque2;
                        $nuevoConteo->id_usuarios = $userToken->id;
                        $nuevoConteo->conteo = 1;
                        $nuevoConteo->save();

                        // Que ID tiene nivel 1 del insignia Planes Finalizados
                        // SIEMPRE EXISTE NIVEL 1
                        $infoIdNivel = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque2)
                            ->where('nivel', 1)
                            ->first();

                        // hito - por defecto nivel 1
                        $nuevoHito = new InsigniasUsuariosDetalle();
                        $nuevoHito->id_niveles_insignias = $infoIdNivel->id;
                        $nuevoHito->id_usuarios = $userToken->id;
                        $nuevoHito->fecha = $fechaCarbon;
                        $nuevoHito->save();


                        // REGISTRAR QUE FINALIZO PLAN USUARIO
                        $finalizado = new PlanesFinalizadosUsuario();
                        $finalizado->id_planes = $infoPlanesBloques->id_planes;
                        $finalizado->id_usuario = $userToken->id;
                        $finalizado->save();


                        if($hayIdOne){
                            // GANE INSIGNIA PLANES FINALIZADOS
                            $datosRaw = $this->retornoTitulosNotificaciones(5, $idiomaTexto);
                            $tiNo = $datosRaw['titulo'];
                            $desNo = $datosRaw['descripcion'];


                            // Guardar Historial Notificacion Usuario
                            $notiHistorial = new NotificacionUsuario();
                            $notiHistorial->id_usuario = $userToken->id;
                            $notiHistorial->id_tipo_notificacion = 5; // POR GANAR PRIMERA INSIGNIA PLAN FINALIZADO
                            $notiHistorial->fecha = $fechaCarbon;
                            $notiHistorial->save();


                            // como es primera vez, se necesita enviar notificacion
                            if($permitirNotificacion) {
                                dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                            }
                        }
                    }
                }






                // ******************* BLOQUE 3 - PLANES COMPARTIDOS EN GRUPOS ***********************
                if($planCompletado){

                    $arrayPlanesAmigos = DB::table('planes_usuarios AS p')
                        ->join('planes_amigos_detalle AS d', 'd.id_planes_usuarios', '=', 'p.id')
                        ->select('p.id_usuario AS idusuariogana', 'd.id_comunidad_solicitud')
                        ->where('d.id_usuario', $userToken->id)
                        ->where('p.id_planes', $infoPlanesBloques->id_planes)
                        ->get();


                    $idTipoInsigniaBloque3 = 5; // planes compartidos en grupos

                    // verificar a quien se sumara puntos
                    foreach ($arrayPlanesAmigos as $dato){


                        // fecha del que gana puntos
                        $fechaCarbonGana = $this->retornoZonaHorariaDepaCarbonNow($dato->idusuariogana);


                        // solo solicitudes aceptadas
                        $infoComuni = ComunidadSolicitud::where('id', $dato->id_comunidad_solicitud)->first();

                        if($infoComuni->estado == 1){

                            // aqui va la insignia de planes compartidos en grupos y al finalizar todos se envia notificacion


                            // OBTENER PILA ONE SIGNAL DEL USUARIO QUE GANO PUNTOS
                            $infoUsuarioFila = Usuarios::where('id', $dato->idusuariogana)->first();
                            $idOneSignalUsuario = $infoUsuarioFila->onesignal;
                            $idiomaUsuarioNoti = $infoUsuarioFila->idioma_noti;

                            $hayIdOne = false;

                            if($idOneSignalUsuario != null){
                                if(strlen($idOneSignalUsuario) == 0){
                                    // vacio no hacer nada
                                }else{
                                    $hayIdOne = true;
                                }
                            }






                            if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsigniaBloque3)
                                ->where('id_usuario', $dato->idusuariogana)->first()){
                                //ya esta registrado, se debera sumar un punto

                                // AQUI SE SUMA CONTADOR Y SE VERIFICA SI GANARA EL HITO

                                $infoConteo = InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsigniaBloque3)
                                    ->where('id_usuarios', $dato->idusuariogana)
                                    ->first();

                                $conteo = $infoConteo->conteo;
                                $conteo++;

                                $arrayNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque3)
                                    ->orderBy('nivel', 'ASC')
                                    ->get();

                                $enviarNoti = false;



                                // verificar si ya alcanzo nivel
                                foreach ($arrayNiveles as $datoNivel){

                                    if($conteo >= $datoNivel->nivel){
                                        // pero verificar que no este el hito registrado
                                        if(InsigniasUsuariosDetalle::where('id_niveles_insignias', $datoNivel->id)
                                            ->where('id_usuarios', $dato->idusuariogana)
                                            ->first()){
                                            // no hacer nada porque ya esta el hito, se debe seguir el siguiente nivel

                                        }else{
                                            $enviarNoti = true;

                                            // registrar hito - nivel y salir bucle
                                            $nuevoDeta = new InsigniasUsuariosDetalle();
                                            $nuevoDeta->id_niveles_insignias = $datoNivel->id;
                                            $nuevoDeta->id_usuarios = $dato->idusuariogana;
                                            $nuevoDeta->fecha = $fechaCarbonGana;
                                            $nuevoDeta->save();

                                            // SUBIO NIVEL HITO - PLANES COMPARTIDOS EN GRUPOS
                                            $notiHistorial = new NotificacionUsuario();
                                            $notiHistorial->id_usuario = $dato->idusuariogana;
                                            $notiHistorial->id_tipo_notificacion = 10;
                                            $notiHistorial->fecha = $fechaCarbonGana;
                                            $notiHistorial->save();

                                            break;
                                        }
                                    }
                                }

                                if($enviarNoti){

                                    if($hayIdOne){
                                        // SUBI DE NIVEL INSIGNIA PLANES FINALIZADOS EN GRUPOS
                                        $datosRaw = $this->retornoTitulosNotificaciones(10, $idiomaUsuarioNoti);
                                        $tiNo = $datosRaw['titulo'];
                                        $desNo = $datosRaw['descripcion'];

                                        // como es primera vez, se necesita enviar notificacion
                                        if($permitirNotificacion) {
                                            dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                                        }
                                    }
                                }

                                // maximo nivel
                                $maxNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque3)->max('nivel');

                                if($conteo <= $maxNiveles){

                                    // solo actualizar conteo

                                    InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsigniaBloque3)
                                        ->where('id_usuarios', $dato->idusuariogana)
                                        ->update(['conteo' => $conteo]);
                                }

                            }else{

                                // PRIMERA VEZ GANANDO INSIGNIA

                                $nuevaInsignia = new InsigniasUsuarios();
                                $nuevaInsignia->id_tipo_insignia = $idTipoInsigniaBloque3; // planes compartidos
                                $nuevaInsignia->id_usuario = $dato->idusuariogana;
                                $nuevaInsignia->fecha = $fechaCarbonGana;
                                $nuevaInsignia->save();


                                $nuevoConteo = new InsigniasUsuariosConteo();
                                $nuevoConteo->id_tipo_insignia = $idTipoInsigniaBloque3;
                                $nuevoConteo->id_usuarios = $dato->idusuariogana;
                                $nuevoConteo->conteo = 1;
                                $nuevoConteo->save();

                                // Que ID tiene nivel 1 del insignia planes compartidos en grupos
                                // SIEMPRE EXISTE NIVEL 1
                                $infoIdNivel = NivelesInsignias::where('id_tipo_insignia', $idTipoInsigniaBloque3)
                                    ->where('nivel', 1)
                                    ->first();

                                // hito - por defecto nivel 1
                                $nuevoHito = new InsigniasUsuariosDetalle();
                                $nuevoHito->id_niveles_insignias = $infoIdNivel->id;
                                $nuevoHito->id_usuarios = $dato->idusuariogana;
                                $nuevoHito->fecha = $fechaCarbonGana;
                                $nuevoHito->save();

                                if($hayIdOne){
                                    // GANE INSIGNIA PLAN COMPARTIDO EN GRUPO
                                    $datosRaw = $this->retornoTitulosNotificaciones(9, $idiomaUsuarioNoti);
                                    $tiNo = $datosRaw['titulo'];
                                    $desNo = $datosRaw['descripcion'];


                                    // Guardar Historial Notificacion Usuario
                                    $notiHistorial = new NotificacionUsuario();
                                    $notiHistorial->id_usuario = $dato->idusuariogana;
                                    $notiHistorial->id_tipo_notificacion = 9; // POR GANAR PRIMERA INSIGNIA PLAN COMPARTIDO EN GRUPO
                                    $notiHistorial->fecha = $fechaCarbonGana;
                                    $notiHistorial->save();


                                    // como es primera vez, se necesita enviar notificacion
                                    if($permitirNotificacion) {
                                        dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                                    }
                                }
                            }
                        }
                    }
                }




                DB::commit();
                return ['success' => 2,
                    'plancompletado' => $planCompletado
                ];

            }catch(\Throwable $e){
                Log::info("error" . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
    }






    // informacion de un cuestionario y sus preguntas de un bloque detalle
    public function informacionCuestionarioBloque(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required', // tabla: planes_block_detalle
            'idiomaplan' => 'required'
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

            $redireccionar = 0;
            $iddevobiblia = 0;

            $infoPlBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first();




            // buscar el
            if($info = DevocionalBiblia::where('id_bloque_detalle', $request->idblockdeta)->first()){

                // Esto se utilizara si redireccionar es igual a 1
                $iddevobiblia = $info->id;

                // hoy ver si tiene algo dentro, aunque sea 1 capitulo
                if(DevocionalCapitulo::where('id_devocional_biblia', $info->id)->first()){
                    $redireccionar = 1;
                }

            }

            // comprueba que al menos haya un cuestionario disponible de cualquier idioma
            if(BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idblockdeta)
                ->first()){

                $datosArray = $this->retornoTituloCuestionarioIdioma($request->idblockdeta, $idiomaTextos);
                $devocional = $datosArray['devocional'];

                return ['success' => 1,
                        'redireccionar' => $redireccionar,
                        'iddevobiblia' => $iddevobiblia,
                        'devocional' => $devocional,

                        'redirecweb' => $infoPlBlockDeta->redireccionar_web,
                        'urllink' => $infoPlBlockDeta->url_link
                ];
            }else{

                return ['success' => 2,
                    'msg' => "No hay cuestionario"
                    ];
            }


        }else{
            return ['success' => 99];
        }
    }

    // RETORNA TEXTO DE UN CUESTIONARIO SEGUN IDIOMA
    private function retornoTituloCuestionarioIdioma($idBlockDeta, $idiomaTexto){

        if($infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            // DEVOLVER CON FORMATO


            // ****************************************************************************************



            $contenidoTitulo = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $datosFuentes = new FuentesCssLetra();
            $contenidoTitulo .= $datosFuentes->retornaFuentesCss();

            $contenidoTitulo .= "

                        </style>
                        <script type='text/javascript'>


                           function notifyClick() {
                                Android.notifyClickToJava(); // Esta función será llamada cuando el párrafo sea tocado
                           }


                        </script>
                    </head>
                    <body>";

            // Titulo

            $contenidoTitulo .= "<div id='miParrafo' onclick='notifyClick()'>";
            $contenidoTitulo .= $infoTituloTexto->titulo;
            $contenidoTitulo .= "</div>";

            // Devocional

            $contenidoTitulo .= $infoTituloTexto->titulo_dia;

            $contenidoTitulo .= "</body>
                    </html>";


            return ['devocional' => $contenidoTitulo
            ];


        }else{
            // si no encuentra sera por defecto español
            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();



            // ****************************************************************************************


            $contenidoTitulo = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $datosFuentes = new FuentesCssLetra();
            $contenidoTitulo .= $datosFuentes->retornaFuentesCss();

            $contenidoTitulo .= "

                        </style>
                        <script type='text/javascript'>


                           function notifyClick() {
                                Android.notifyClickToJava(); // Esta función será llamada cuando el párrafo sea tocado
                           }


                        </script>
                    </head>
                    <body>";

            // Titulo

            $contenidoTitulo .= "<div id='miParrafo' onclick='notifyClick()'>";
            $contenidoTitulo .= $infoTituloTexto->titulo;
            $contenidoTitulo .= "</div>";

            // Devocional

            $contenidoTitulo .= $infoTituloTexto->titulo_dia;

            $contenidoTitulo .= "</body>
                    </html>";


            return ['devocional' => $contenidoTitulo
            ];

        }
    }



    // informacion de preguntas de un bloque detalle
    public function informacionPreguntasBloque(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
            'idiomaplan' => 'required'
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

            // comprueba que al menos haya una pregunta disponible
            if($infoBloquePre = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)->first()){

                $descripcionPregunta = $this->retornoTituloPrincipalPreguntaTextoIdioma($request->idblockdeta, $idiomaTextos);


                $arrayBloque = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                    ->orderBy('posicion')
                    ->get();

                foreach ($arrayBloque as $dato){

                    // informacion imagen
                    $imagenData = ImagenPreguntas::where('id', $dato->id_imagen_pregunta)->first();
                    $dato->imagen = $imagenData->imagen;


                    $titulo = $this->retornoTituloPreguntaTextoIdioma($dato->id, $idiomaTextos);
                    $dato->titulo = $titulo;


                    $texto = "";
                    // buscar texto de la pregunta contestada si existe
                    if($detaPre = BloquePreguntasUsuarios::where('id_bloque_preguntas', $dato->id)
                        ->where('id_usuarios', $userToken->id)
                        ->first()){
                        $texto = $detaPre->texto;
                    }

                    $dato->texto = $texto;
                }

                // verificar si hay preguntas ya guardadas
                $hayrespuesta = 0;
                if(BloquePreguntasUsuarios::where('id_bloque_preguntas', $infoBloquePre->id)
                    ->where('id_usuarios', $userToken->id)
                    ->first()){
                    $hayrespuesta = 1;
                }

                $infoPlanBlockDetalle = PlanesBlockDetalle::where('id', $infoBloquePre->id_plan_block_detalle)->first();

                return ['success' => 1,
                    'descripcion' => $descripcionPregunta,
                    'hayrespuesta' => $hayrespuesta,
                    'listado' => $arrayBloque,
                    'genero' => $userToken->id_genero,
                    'ignorarshare' => $infoPlanBlockDetalle->ignorar_pregunta
                ];
            }else{

                return ['success' => 2,
                    'msg' => "No hay preguntas"
                ];
            }


        }else{
            return ['success' => 99];
        }
    }


    // RETORNA TITULO PRINCIPAL QUE SE MUESTRA 1 SOLA VEZ PARA BLOQUE PREGUNTAS

    private function retornoTituloPrincipalPreguntaTextoIdioma($idBlockDeta, $idiomaTexto){

        if($infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDeta)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return  $infoTituloTexto->titulo_pregunta;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return  $infoTituloTexto->titulo_pregunta;
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


    // GUARDADR LAS PREGUNTAS O ACTUALIZA - VERSION ANDROID
    public function actualizarPreguntasUsuarioPlan(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
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

            $zonaHorariaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);

            DB::beginTransaction();

            try {

                // COMO GUARDO PREGUNTAS, GUARDAR RACHA DEVOCIONAL
                // SE GUARDA LOS ITEMS
                // TAN SOLO QUE HAYA 1 PREGUNTA GUARDADA SE VERIFICARA LAS INSIGNIAS
                if(RachaDevocional::where('id_usuario', $userToken->id)
                    ->where('id_plan_block_deta', $request->idblockdeta)->first()){
                    // no guardar
                }else{

                    // GUARDAR UNA RACHA DEVOCIONAL
                    $nuevaRacha = new RachaDevocional();
                    $nuevaRacha->id_usuario = $userToken->id;
                    $nuevaRacha->id_plan_block_deta = $request->idblockdeta; // son los items
                    $nuevaRacha->fecha = $zonaHorariaCarbon;
                    $nuevaRacha->save();
                }

                if ($request->has('idpregunta')) {

                    foreach ($request->idpregunta as $clave => $valor) {

                        if($infoFila = BloquePreguntasUsuarios::where('id_bloque_preguntas', $clave)
                            ->where('id_usuarios', $userToken->id)->first()){

                            // actualizar la preguntas
                            BloquePreguntasUsuarios::where('id', $infoFila->id)
                                ->update([
                                    'texto' => $valor['txtpregunta'],
                                    'fecha_actualizo' => $zonaHorariaCarbon
                                ]);

                        }else{
                            $pregunta = new BloquePreguntasUsuarios();
                            $pregunta->id_bloque_preguntas = $clave;
                            $pregunta->id_usuarios = $userToken->id;
                            $pregunta->texto = $valor['txtpregunta'];
                            $pregunta->fecha = $zonaHorariaCarbon;
                            $pregunta->fecha_actualizo = null;
                            $pregunta->save();
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





   // VERSION IPHONE
    public function actualizarPreguntasUsuarioPlanIphone(Request $request)
    {

        Log::info($request->all());

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
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

            $zonaHorariaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);

            DB::beginTransaction();

            try {

                // SON LOS ITEMS
                if(RachaDevocional::where('id_usuario', $userToken->id)
                  ->where('id_plan_block_deta', $request->idblockdeta)->first()){
                  // no guardar
                  }else{
                      // GUARDAR UNA RACHA DEVOCIONAL
                      $nuevaRacha = new RachaDevocional();
                      $nuevaRacha->id_usuario = $userToken->id;
                      $nuevaRacha->id_plan_block_deta = $request->idblockdeta; // ITEMS
                      $nuevaRacha->fecha = $zonaHorariaCarbon;
                      $nuevaRacha->save();
                  }


              if ($request->has('datos')) {

                  foreach ($request->datos as $dato) {

                      $clave = $dato['id']; // ID PREGUNTA
                      $valor = $dato['estado']; // TEXTO DEL EDT


                      if($infoFila = BloquePreguntasUsuarios::where('id_bloque_preguntas', $clave)
                          ->where('id_usuarios', $userToken->id)->first()){

                          // actualizar la preguntas
                          BloquePreguntasUsuarios::where('id', $infoFila->id)
                              ->update([
                                  'texto' => $valor,
                                  'fecha_actualizo' => $zonaHorariaCarbon
                              ]);

                      }else{
                          $pregunta = new BloquePreguntasUsuarios();
                          $pregunta->id_bloque_preguntas = $clave;
                          $pregunta->id_usuarios = $userToken->id;
                          $pregunta->texto = $valor;
                          $pregunta->fecha = $zonaHorariaCarbon;
                          $pregunta->fecha_actualizo = null;
                          $pregunta->save();
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



    public function informacionPreguntasParaCompartir(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
            'idiomaplan' => 'required'
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


            DB::beginTransaction();

            try {

                // comprueba que al menos haya una pregunta disponible
                if(BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)->first()){


                    // VERIFICAR QUE HAYA CONTESTADO YA PREGUNTAS

                    // comprobar si hay preguntas, por lo menos 1 visible y requerida
                    $arrayPreguntasVe = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)->get();

                    $hayVerificar = false;
                    $hayRespondidas = false;

                    // verificar que haya al menos preguntas ya guardadas
                    foreach ($arrayPreguntasVe as $dato){
                        $hayVerificar = true;

                        if(BloquePreguntasUsuarios::where('id_bloque_preguntas', $dato->id)
                            ->where('id_usuarios', $userToken->id)->first()){
                            $hayRespondidas = true;
                            break;
                        }
                    }


                    if($hayVerificar){
                        if(!$hayRespondidas){
                            return ['success' => 1, 'msg' => "falta responder preguntas"];
                        }
                    }


                    $arrayBloque = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                        ->orderBy('posicion')
                        ->get();


                    $formatoPreguntaOrdenada = "";


                    foreach ($arrayBloque as $dato){

                        // informacion imagen
                        $imagenData = ImagenPreguntas::where('id', $dato->id_imagen_pregunta)->first();
                        $dato->imagen = $imagenData->imagen;


                        $titulo = $this->retornoTituloPreguntaTextoIdioma($dato->id, $idiomaTextos);
                        $dato->titulo = $titulo;

                        $texto = "";
                        // buscar texto de la pregunta contestada si existe
                        if($detaPre = BloquePreguntasUsuarios::where('id_bloque_preguntas', $dato->id)
                            ->where('id_usuarios', $userToken->id)
                            ->first()){
                            $texto = $detaPre->texto;
                        }
                        $dato->texto = $texto;

                        $crawler = new Crawler($titulo);
                        $textHtml = $crawler->text();

                        $formatoPreguntaOrdenada = $formatoPreguntaOrdenada . $textHtml . "\n R// " . $texto . "\n\n";
                    }

                    $descrip = $this->retornoTituloBloquesTextos($idiomaTextos, $request->idblockdeta);


                    // ACTUALIZAR INSIGNIA COMPARTIR DEVOCIONAL


                    $idTipoInsignia = 2; // COMPARTIR DEVOCIONAL

                    $idiomaTexto = $request->idiomaplan;
                    $fechaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);


                    $infoUsuarioFila = Usuarios::where('id', $userToken->id)->first();
                    $idOneSignalUsuario = $infoUsuarioFila->onesignal;

                    $hayIdOne = false;

                    if($idOneSignalUsuario != null){
                        if(strlen($idOneSignalUsuario) == 0){
                            // vacio no hacer nada
                        }else{
                            $hayIdOne = true;
                        }
                    }

                    // COMPARTIR DEVOCIONAL
                    if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsignia)
                        ->where('id_usuario', $userToken->id)->first()){
                        //ya esta registrado, se debera sumar un punto

                        // AQUI SE SUMA CONTADOR Y SE VERIFICA SI GANARA EL HITO

                        $infoConteo = InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsignia)
                            ->where('id_usuarios', $userToken->id)
                            ->first();

                        $conteo = $infoConteo->conteo;
                        $conteo++;

                        $arrayNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)
                            ->orderBy('nivel', 'ASC')
                            ->get();

                        $enviarNoti = false;


                        // verificar si ya alcanzo nivel
                        foreach ($arrayNiveles as $dato){

                            if($conteo >= $dato->nivel){
                                // pero verificar que no este el hito registrado
                                if(InsigniasUsuariosDetalle::where('id_niveles_insignias', $dato->id)
                                    ->where('id_usuarios', $userToken->id)
                                    ->first()){
                                    // no hacer nada porque ya esta el hito, se debe seguir el siguiente nivel

                                }else{
                                    $enviarNoti = true;

                                    // registrar hito - nivel y salir bucle
                                    $nuevoDeta = new InsigniasUsuariosDetalle();
                                    $nuevoDeta->id_niveles_insignias = $dato->id;
                                    $nuevoDeta->id_usuarios = $userToken->id;
                                    $nuevoDeta->fecha = $fechaCarbon;
                                    $nuevoDeta->save();


                                    // SUBIO NIVEL INSIGNIA COMPARTIR DEVOCIONAL
                                    $notiHistorial = new NotificacionUsuario();
                                    $notiHistorial->id_usuario = $userToken->id;
                                    $notiHistorial->id_tipo_notificacion = 4;
                                    $notiHistorial->fecha = $fechaCarbon;
                                    $notiHistorial->save();

                                    break;
                                }
                            }
                        }

                        if($enviarNoti){

                            if($hayIdOne){
                                // SUBI DE NIVEL INSIGNIA COMPARTIR DEVOCIONAL
                                $datosRaw = $this->retornoTitulosNotificaciones(4, $idiomaTexto);
                                $tiNo = $datosRaw['titulo'];
                                $desNo = $datosRaw['descripcion'];

                                // como es primera vez, se necesita enviar notificacion
                                dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                            }
                        }

                        // maximo nivel
                        $maxNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)->max('nivel');

                        if($conteo <= $maxNiveles){

                            // solo actualizar conteo

                            InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsignia)
                                ->where('id_usuarios', $userToken->id)
                                ->update(['conteo' => $conteo]);

                        }

                    }else{

                        // PRIMERA VEZ GANANDO INSIGNIA

                        $nuevaInsignia = new InsigniasUsuarios();
                        $nuevaInsignia->id_tipo_insignia = $idTipoInsignia;
                        $nuevaInsignia->id_usuario = $userToken->id;
                        $nuevaInsignia->fecha = $fechaCarbon;
                        $nuevaInsignia->save();



                        $nuevoConteo = new InsigniasUsuariosConteo();
                        $nuevoConteo->id_tipo_insignia = $idTipoInsignia;
                        $nuevoConteo->id_usuarios = $userToken->id;
                        $nuevoConteo->conteo = 1;
                        $nuevoConteo->save();

                        // Que ID tiene nivel 1 del insignia compartir App
                        // SIEMPRE EXISTE NIVEL 1
                        $infoIdNivel = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)
                            ->where('nivel', 1)
                            ->first();

                        // hito - por defecto nivel 1
                        $nuevoHito = new InsigniasUsuariosDetalle();
                        $nuevoHito->id_niveles_insignias = $infoIdNivel->id;
                        $nuevoHito->id_usuarios = $userToken->id;
                        $nuevoHito->fecha = $fechaCarbon;
                        $nuevoHito->save();



                        // INSIGNIA COMPARTIR DEVOCIONAL
                        $notiHistorial = new NotificacionUsuario();
                        $notiHistorial->id_usuario = $userToken->id;
                        $notiHistorial->id_tipo_notificacion = 3;
                        $notiHistorial->fecha = $fechaCarbon;
                        $notiHistorial->save();




                        if($hayIdOne){
                            // SUBI DE NIVEL INSIGNIA COMPARTIR DEVOCIONAL
                            $datosRaw = $this->retornoTitulosNotificaciones(3, $idiomaTexto);
                            $tiNo = $datosRaw['titulo'];
                            $desNo = $datosRaw['descripcion'];

                            // como es primera vez, se necesita enviar notificacion
                            dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
                        }
                    }


                    DB::commit();

                    return ['success' => 2,
                        'descripcion' => $descrip,
                        'listado' => $arrayBloque,
                        'formatoPregu' => $formatoPreguntaOrdenada
                    ];
                }else{

                    return ['success' => 3,
                        'msg' => "No hay preguntas, que esten activas"
                    ];
                }
            }catch(\Throwable $e){
                Log::info("error: " . $e);
                DB::rollback();
                return ['success' => 99];
            }
        }else{
            return ['success' => 99];
        }
    }





    // RETORNO TITULO Y DESCRIPCION PARA NOTIFICACIONES ENVIADAS EN PUSH
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





    private function retornoTextoPersonalizadoPlan($idiomaPlan, $idPlanesBloques)
    {
        if($info = PlanesBloquesTextos::where('id_planes_bloques', $idPlanesBloques)
            ->where('id_idioma_planes', $idiomaPlan)
            ->first()){

            return $info->titulo;

        }else{
            // si no encuentra sera por defecto español

            if($info = PlanesBloquesTextos::where('id_planes_bloques', $idPlanesBloques)
                ->where('id_idioma_planes', 1)
                ->first()){
                return $info->titulo;
            }else{
                return "";
            }
        }
    }



    // RETORNO DE ZONA HORARIA SEGUN DEPARTAMENTO
    private function retornoZonaHorariaDepaCarbonNow($idigleisa)
    {
        $infoIglesia = Iglesias::where('id', $idigleisa)->first();
        $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
        $infoZonaHorarioa = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();
        return Carbon::now($infoZonaHorarioa->zona);
    }




    // FIX 30/04/2024
    public function listadoMisPlanesNoPaginacion(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {


            $arrayPlanesUser = DB::table('planes AS p')
                ->join('planes_usuarios AS pu', 'pu.id_planes', '=', 'p.id')
                ->select('pu.id_usuario', 'pu.id_planes')
                ->where('pu.id_usuario', $userToken->id)
                ->orderBy('pu.id_planes', 'ASC')
                ->get();


            foreach ($arrayPlanesUser as $dato){
                $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                $dato->titulo = $titulosRaw['titulo'];
                $dato->subtitulo = $titulosRaw['subtitulo'];

                $infoP = Planes::where('id', $dato->id_planes)->first();
                $dato->idplan = $infoP->id;

                if($idiomaTextos == 1){
                    $dato->imagen = $infoP->imagen;
                    $dato->imagenportada = $infoP->imagenportada;
                }else{
                    $dato->imagen = $infoP->imagen_ingles;
                    $dato->imagenportada = $infoP->imagenportada_ingles;
                }
            }

            $hayinfo = 0;

            // EN LA APP: se verifica la primera vez con un boolean
            if ($arrayPlanesUser->count() > 0) {
                $hayinfo = 1;
            }

            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayPlanesUser,
            ];

        }else{
            return ['success' => 99];
        }
    }



    public function buscarPlanesNoAgregadosNoPaginacion(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {


            // todos los planes de mi usuario
            $arrayIdYaSeleccionados = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();


            // conocer si habra planes disponibles
            $hayInfo = 0;

            // obtener todos los planes NO elegido por el usuario y sean visible
            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)->get();

            if ($arrayPlanes->isNotEmpty()) {
                $hayInfo = 1;
            }


            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)->get();

            foreach ($arrayPlanes as $dato){
                $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id);
                $dato->titulo = $arrayRaw['titulo'];
                $dato->subtitulo = $arrayRaw['subtitulo'];


                // modificar la imagen y la portada segun idioma
                if($idiomaTextos == 2){
                    $dato->imagen = $dato->imagen_ingles;
                    $dato->imagenportada = $dato->imagenportada_ingles;
                }
            }

            return [
                'success' => 1,
                'hayinfo' => $hayInfo,
                'listado2' => $arrayPlanes
            ];
        }else{
            return ['success' => 99];
        }
    }



    public function borrarListadoNotificaciones(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');


        if ($userToken = JWTAuth::user($tokenApi)) {

            DB::beginTransaction();

            try {
                NotificacionUsuario::where('id_usuario', $userToken->id)->delete();

                DB::commit();
                return ['success' => 1];
            }catch(\Throwable $e){
                Log::info('error: ' . $e);
                DB::rollback();
                return ['success' => 99];
            }

        }else{
            return ['success' => 1];
        }
    }




}
