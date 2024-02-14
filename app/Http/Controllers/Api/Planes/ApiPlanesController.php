<?php

namespace App\Http\Controllers\Api\Planes;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacion;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\ImagenPreguntas;
use App\Models\InsigniasUsuarios;
use App\Models\InsigniasUsuariosConteo;
use App\Models\InsigniasUsuariosDetalle;
use App\Models\NivelesInsignias;
use App\Models\NotificacionTextos;
use App\Models\NotificacionUsuario;
use App\Models\Planes;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBlockDetaUsuario;
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

            // obtener todos los planes NO elegido por el usuario y sean visible
            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)
                ->where('visible', 1)
                ->get();

            if ($arrayPlanes->isNotEmpty()) {
                $hayInfo = 1;
            }


            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);


            $arrayPlanes = Planes::whereNotIn('id', $arrayIdYaSeleccionados)
                ->where('visible', 1)
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

            $idPlanContinuar = 0;
            $arrayContinuar = null;
            $haycontinuar = 0;

            $arrayPlanUsuario = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            foreach ($arrayPlanUsuario as $dato){

                $arrayPlanBloque = PlanesBloques::where('id_planes', $dato->id_planes)->get();
                $planCompletado = 1;

                if($arrayPlanBloque != null && $arrayPlanBloque->isNotEmpty()){
                    foreach ($arrayPlanBloque as $rec){ // cada bloque

                        // buscar el detalle de ese bloque
                        $arrayListado = PlanesBlockDetalle::where('id_planes_bloques', $rec->id)
                            ->where('visible', 1)
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
                }else{
                    $planCompletado = 0;
                }

                $dato->plancompletado = $planCompletado;
            }


            // obtener los planes que no esten completados
            $pilaIdPlanNoComplet = array();

            foreach ($arrayPlanUsuario as $item){
                if($item->plancompletado == 0){
                    array_push($pilaIdPlanNoComplet, $item->id_planes);
                }
            }


            // filtrar plan continuar, aunque haya, evitar el completados
            if($infoContinuar = PlanesUsuariosContinuar::where('id_usuarios', $userToken->id)
                ->whereIn('id_planes', $pilaIdPlanNoComplet)
                ->first()){
                $haycontinuar = 1;
                $idPlanContinuar = $infoContinuar->id_planes;

                // plan ultimo para continuar
                $arrayContinuar = PlanesUsuariosContinuar::where('id_usuarios', $userToken->id)
                    ->take(1) // por seguridad tomar solo 1
                    ->get();

                foreach ($arrayContinuar as $dato){
                    $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                    $dato->titulo = $titulosRaw['titulo'];
                    $dato->subtitulo = $titulosRaw['subtitulo'];

                    $infoP = Planes::where('id', $dato->id_planes)->first();
                    $dato->imagen = $infoP->imagen;
                    $dato->imagenportada = $infoP->imagenportada;
                    $dato->idplan = $infoP->id;
                }
            }



            // ***** AQUI INICIA LA PAGINACION *****

            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);


            $arrayPlanesUser = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->whereIn('id_planes', $pilaIdPlanNoComplet)
                ->whereNotIn('id_planes', [$idPlanContinuar])
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
                'haycontinuar' => $haycontinuar,
                'hayinfo' => $hayinfo,
                'listacontinuar' => $arrayContinuar, // no usa barra progreso
                'listado' => $arrayPlanesUser,
            ];

        }else{
            return ['success' => 99];
        }
    }

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

            // obtener zona horaria
            $infoIglesia = Iglesias::where('id', $userToken->id_iglesia)->first();
            $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
            $zonaHoraria = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();

            // obtener todos los bloques ordenados por fecha
            $arrayBloques = PlanesBloques::where('id_planes', $request->idplan)
                ->where('visible', 1)
                ->orderBy('fecha_inicio', 'ASC')
                ->get();

            $contador = 0;
             $resultsBloque = array();
             $index = 0;

            // con esto se conoce si hay un dia con informacion para Hoy, sino se tomara el ultimo
            // en la aplicacion
            $hayDiaActual = 0;


            foreach ($arrayBloques as $dato){
                array_push($resultsBloque, $dato);

                $contador++;
                $dato->abreviatura = $this->retorno3LetrasFechasIdioma($idiomaTextos, $dato->fecha_inicio);
                $dato->contador = $contador;

                // fecha inicio del bloque
                $fecha1 = Carbon::parse($dato->fecha_inicio);

                // fecha-horario actual segun usuario zona horaria
                $fecha2 = Carbon::parse(now(), $zonaHoraria->zona);


                if($fecha1->isSameDay($fecha2)){
                    $dato->mismodia = 1;
                    $hayDiaActual = 1;
                }else{
                    $dato->mismodia = 0;
                }

                // ESTA PARTE ERA QUE APARECIERA EL BLOQUE SEGUN FECHA DEL USUARIO
                /*if($dato->esperar_fecha == 1){

                    //gte: mayor o igual
                    if($fecha2->gte($fecha1)){
                        // SET
                        $dato->esperar_fecha = 0;
                    }
                }*/


                // TEXTO PERSONALIZADO EN EL BLOQUE

                $textoPersonalizado = "";
                // buscar si tiene texto personalizado, para no mostrar la fecha
                if($dato->texto_personalizado == 1){
                    $textoPersonalizado = $this->retornoTextoPersonalizadoPlan($idiomaTextos, $dato->id_planes);
                }

                $dato->textopersonalizado = $textoPersonalizado;

                // agregar detalle bloques
                $arrayDetaBloque = PlanesBlockDetalle::where('id_planes_bloques', $dato->id)
                    ->where('visible', 1)
                    ->orderBy('posicion', 'ASC')
                    ->get();

                foreach ($arrayDetaBloque as $datoArr){

                    $datoArr->titulo = $this->retornoTituloBloquesTextos($idiomaTextos, $datoArr->id);

                    // saber si esta check para mi usuario
                    if($infoDeta = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes_block_deta', $datoArr->id)
                    ->first()){
                        if($infoDeta->completado == 1){
                            $datoArr->completado = 1;
                        }else{
                            $datoArr->completado = 0;
                        }
                    }else{
                        $datoArr->completado = 0;
                    }

                    // verificar si tiene preguntas, o alguna activa para mostrarlas
                    $hayPreguntas = 0;
                    $arrayPreguntas = BloquePreguntas::where('id_plan_block_detalle', $datoArr->id)
                        ->where('visible', 1)
                        ->count();

                    if ($arrayPreguntas > 0) {
                        $hayPreguntas = 1;
                    }
                    $datoArr->tiene_preguntas = $hayPreguntas;
                }

                $resultsBloque[$index]->detalle = $arrayDetaBloque;
                $index++;
            }


            // Para comparar en la aplicacion que sino hay info para este dia, este id sera el bloque que se
            // le cambiara el estilo
            $idUltimoBloque = 0;

            if ($arrayBloques->isNotEmpty() && $hayDiaActual == 0) {

                $encontroBloque = true;

                // encontrar cual es el siguiente bloque que deberia cargarse
                foreach ($arrayBloques as $bloque){
                    $fecha1 = Carbon::parse($bloque->fecha_inicio);
                    $fecha2 = Carbon::parse(now(), $zonaHoraria->zona);

                    if($fecha1->gte($fecha2)){
                        $idUltimoBloque = $bloque->id;
                        $encontroBloque = false;
                        break;
                    }
                }

                if($encontroBloque){
                    $ultimoElemento = $arrayBloques->last();
                    $idUltimoBloque = $ultimoElemento->id;
                }
            }


            return ['success' => 1,
                'portada' => $infoPlan->imagenportada,
                'haydiaactual' => $hayDiaActual,
                'idultimobloque' => $idUltimoBloque,
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
            'idplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            DB::beginTransaction();

            try {

                $activarNotificaciones = true;


                // comprobar si hay preguntas, por lo menos 1 visible y requerida
                $arrayPreguntas = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                    ->where('visible', 1)
                    ->where('requerido', 1)
                    ->get();

                $hayVerificar = false;
                $hayRespondidas = false;

                // verificar que haya al menos preguntas ya guardadas
                foreach ($arrayPreguntas as $dato){
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



                //*******************************


                $planCompletado = 1;
                $primeraVezItem = false;

                // si existe, solo actualizar
                if($idinfo = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes_block_deta', $request->idblockdeta)
                    ->first()){

                    PlanesBlockDetaUsuario::where('id', $idinfo->id)
                        ->update([
                            'completado' => $request->valor,
                        ]);

                }else{

                    // se debera crear

                    $detalle = new PlanesBlockDetaUsuario();
                    $detalle->id_usuario = $userToken->id;
                    $detalle->id_planes_block_deta = $request->idblockdeta;
                    $detalle->completado = 1;
                    $detalle->save();

                    $primeraVezItem = true;
                }




                // INSIGNIAS


                $idTipoInsignia = 4; // RACHA DEVOCIONAL

                $idiomaTexto = $request->idiomaplan;
                $fechaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);


                $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $userToken->id)->get();
                $pilaOneSignal = array();
                $hayIdOne = false;
                foreach ($arrayOneSignal as $item){
                    if($item->onesignal != null){
                        $hayIdOne = true;
                        array_push($pilaOneSignal, $item->onesignal);
                    }
                }


                // COMPARTIR DEVOCIONAL
                if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsignia)
                    ->where('id_usuario', $userToken->id)->first()){
                    //ya esta registrado, se debera sumar un punto

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
                                    $notiHistorial->tipo_tipo_notificacion = 4;
                                    $notiHistorial->fecha = $fechaCarbon;
                                    $notiHistorial->save();

                                    break;
                                }
                            }
                        }

                        if($enviarNoti){

                            if($hayIdOne){
                                // SUBI DE NIVEL INSIGNIA RACHA DEVOCIONAL
                                $datosRaw = $this->retornoTitulosNotificaciones(8, $idiomaTexto);
                                $tiNo = $datosRaw['titulo'];
                                $desNo = $datosRaw['descripcion'];


                                // como es primera vez, se necesita enviar notificacion
                                if($activarNotificaciones){
                                    dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                                }

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

                    }


                }else{

                    // PRIMERA VEZ GANANDO INSIGNIA RACHA
                    // AQUI ENTRARA 1 SOLA VEZ POR USUARIO

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
                    $notiHistorial->tipo_tipo_notificacion = 3;
                    $notiHistorial->fecha = $fechaCarbon;
                    $notiHistorial->save();



                    if($hayIdOne){
                        // GANE INSIGNIA RACHA DEVOCIONAL
                        $datosRaw = $this->retornoTitulosNotificaciones(7, $idiomaTexto);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];

                        if($activarNotificaciones) {
                            // como es primera vez, se necesita enviar notificacion
                            dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                        }
                    }
                }


                // VERIFICA SI TODAS LAS CASILLAS DE TODOS LOS BLOQUES ESTAN COMPLETOS
                $arrayPlanBloque = PlanesBloques::where('id_planes', $request->idplan)->get();

                foreach ($arrayPlanBloque as $dato){

                    // buscar el detalle de cada bloque
                    $arrayListado = PlanesBlockDetalle::where('id_planes_bloques', $dato->id)
                        ->where('visible', 1)
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


                $infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first();
                $infoPlanesBloques = PlanesBloques::where('id', $infoBlockDeta->id_planes_bloques)->first();



                // VERIFICAR EN OTRO FUNCTION PARA INSIGNIA COMPLETAR PLAN
                if($planCompletado == 1){
                $this->verificarInsigniaCompletarPlan($userToken, $infoPlanesBloques->id_planes, $activarNotificaciones, $idiomaTexto);
                }


                // colocar plan continuar por defecto
                $this->retornoActualizarPlanUsuarioContinuar($userToken->id, $infoPlanesBloques->id_planes);

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


    private function verificarInsigniaCompletarPlan($userToken, $idplanes, $activarNotificaciones, $idiomaTexto){


        $idTipoInsignia = 3; // PLANES FINALIZADOS

        DB::beginTransaction();
        try {

            $fechaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);


            $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $userToken->id)->get();
            $pilaOneSignal = array();
            $hayIdOne = false;
            foreach ($arrayOneSignal as $item){
                if($item->onesignal != null){
                    $hayIdOne = true;
                    array_push($pilaOneSignal, $item->onesignal);
                }
            }


            if(InsigniasUsuarios::where('id_tipo_insignia', $idTipoInsignia)
                ->where('id_usuario', $userToken->id)->first()){



                // SE DEBE EVITAR QUE SE AUMENTE PUNTO POR EL MISMO PLAN
                if(PlanesFinalizadosUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes', $idplanes)->first()){
                    // no registrar nada
                }else{

                    // es nuevo plan finalizado, aumentar punto
                    // AQUI SE SUMA CONTADOR Y SE VERIFICA SI GANARA EL HITO


                    // REGISTRAR QUE FINALIZO PLAN USUARIO
                    $finalizado = new PlanesFinalizadosUsuario();
                    $finalizado->id_planes = $idplanes;
                    $finalizado->id_usuario = $userToken->id;
                    $finalizado->save();


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

                                // SUBIO NIVEL HITO - PLAN FINALIZADO
                                $notiHistorial = new NotificacionUsuario();
                                $notiHistorial->id_usuario = $userToken->id;
                                $notiHistorial->tipo_tipo_notificacion = 6;
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

                            if($activarNotificaciones){
                                dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                            }

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


                }
            }else{

                // PRIMERA VEZ GANANDO INSIGNIA PLAN FINALIZADO

                $nuevaInsignia = new InsigniasUsuarios();
                $nuevaInsignia->id_tipo_insignia = $idTipoInsignia; // compartir App
                $nuevaInsignia->id_usuario = $userToken->id;
                $nuevaInsignia->fecha = $fechaCarbon;
                $nuevaInsignia->save();

                $nuevoConteo = new InsigniasUsuariosConteo();
                $nuevoConteo->id_tipo_insignia = $idTipoInsignia;
                $nuevoConteo->id_usuarios = $userToken->id;
                $nuevoConteo->conteo = 1;
                $nuevoConteo->save();

                // Que ID tiene nivel 1 del insignia Planes Finalizados
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


                // REGISTRAR QUE FINALIZO PLAN USUARIO
                $finalizado = new PlanesFinalizadosUsuario();
                $finalizado->id_planes = $idplanes;
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
                    $notiHistorial->tipo_tipo_notificacion = 5; // POR GANAR PRIMERA INSIGNIA PLAN FINALIZADO
                    $notiHistorial->fecha = $fechaCarbon;
                    $notiHistorial->save();


                    // como es primera vez, se necesita enviar notificacion
                    if($activarNotificaciones) {
                        dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                    }
                }
            }

            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e) {
            DB::rollback();
            Log::info("error: " . $e);
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

            // comprueba que al menos haya un cuestionario disponible de cualquier idioma
            if(BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idblockdeta)
                ->first()){

                $datosArray = $this->retornoTituloCuestionarioIdioma($request->idblockdeta, $idiomaTextos);
                $titulo = $datosArray['titulo'];
                $texto = $datosArray['texto'];
                return ['success' => 1,
                       'titulo' => $titulo,
                       'texto' => $texto
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
            return ['texto' => $infoTituloTexto->texto,
                    'texto_dia' => $infoTituloTexto->texto_dia,
                    'titulo' => $infoTituloTexto->titulo,
                    'titulo_dia' => $infoTituloTexto->titulo_dia
            ];

        }else{
            // si no encuentra sera por defecto español
            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['texto' => $infoTituloTexto->texto,
                'texto_dia' => $infoTituloTexto->texto_dia,
                'titulo' => $infoTituloTexto->titulo,
                'titulo_dia' => $infoTituloTexto->titulo_dia
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
            if($infoBloquePre = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                ->where('visible', 1)
                ->first()){

                $descripcionPregunta = $this->retornoTituloPrincipalPreguntaTextoIdioma($request->idblockdeta, $idiomaTextos);


                $arrayBloque = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                    ->where('visible', 1)
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

                return ['success' => 1,
                    'descripcion' => $descripcionPregunta,
                    'hayrespuesta' => $hayrespuesta,
                    'listado' => $arrayBloque
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



    // guardar las preguntas del usuario segun plan, guardara las que vienen y en actualizar
    // se verifica si existe o no para update o crearla
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
                if(RachaDevocional::where('id_usuario', $userToken->id)
                    ->where('id_plan_block_deta', $request->idblockdeta)->first()){
                    // no guardar
                }else{
                    // GUARDAR UNA RACHA DEVOCIONAL
                    $nuevaRacha = new RachaDevocional();
                    $nuevaRacha->id_usuario = $userToken->id;
                    $nuevaRacha->id_plan_block_deta = $request->idblockdeta;
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

                // setear plan continuar
                $infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first();
                $infoPlanesBloques = PlanesBloques::where('id', $infoBlockDeta->id_planes_bloques)->first();

                // colocar plan continuar por defecto
                $this->retornoActualizarPlanUsuarioContinuar($userToken->id, $infoPlanesBloques->id_planes);


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
                if(BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                    ->where('visible', 1)
                    ->first()){


                    // VERIFICAR QUE HAYA CONTESTADO YA PREGUNTAS

                    // comprobar si hay preguntas, por lo menos 1 visible y requerida
                    $arrayPreguntasVe = BloquePreguntas::where('id_plan_block_detalle', $request->idblockdeta)
                        ->where('visible', 1)
                        ->where('requerido', 1)
                        ->get();

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
                        ->where('visible', 1)
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

                    $infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first();
                    $ignorarpre = $infoBlockDeta->ignorar_pregunta;
                    $descrip = $this->retornoTituloBloquesTextos($idiomaTextos, $request->idblockdeta);



                    // ACTUALIZAR INSIGNIA COMPARTIR DEVOCIONAL


                    $idTipoInsignia = 2; // COMPARTIR DEVOCIONAL

                    $idiomaTexto = $request->idiomaplan;
                    $fechaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);


                    $arrayOneSignal = UsuarioNotificaciones::where('id_usuario', $userToken->id)->get();
                    $pilaOneSignal = array();
                    $hayIdOne = false;
                    foreach ($arrayOneSignal as $item){
                        if($item->onesignal != null){
                            $hayIdOne = true;
                            array_push($pilaOneSignal, $item->onesignal);
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
                                    $notiHistorial->tipo_tipo_notificacion = 4;
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
                                dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
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
                        $notiHistorial->tipo_tipo_notificacion = 3;
                        $notiHistorial->fecha = $fechaCarbon;
                        $notiHistorial->save();




                        if($hayIdOne){
                            // SUBI DE NIVEL INSIGNIA COMPARTIR DEVOCIONAL
                            $datosRaw = $this->retornoTitulosNotificaciones(3, $idiomaTexto);
                            $tiNo = $datosRaw['titulo'];
                            $desNo = $datosRaw['descripcion'];

                            // como es primera vez, se necesita enviar notificacion
                            dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                        }
                    }


                    DB::commit();

                    return ['success' => 2,
                        'descripcion' => $descrip,
                        'listado' => $arrayBloque,
                        'ignorarpre' => $ignorarpre
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




    private function retornoActualizarPlanUsuarioContinuar($iduser, $idplan)
    {
        if($idPlanUser = PlanesUsuariosContinuar::where('id_usuarios', $iduser)->first()){
            // solo actualizar

            PlanesUsuariosContinuar::where('id', $idPlanUser->id)
                ->update([
                    'id_planes' => $idplan,
                ]);
        }
        else{
            // crear
            $dato = new PlanesUsuariosContinuar();
            $dato->id_usuarios = $iduser;
            $dato->id_planes = $idplan;
            $dato->save();
        }
    }


    // informacion de todos los planes completados
    public function listadoMisPlanesCompletados(Request $request)
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

            $arrayPlanUsuario = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();




            foreach ($arrayPlanUsuario as $dato){

                // NO SE FILTRARA POR VISIBLES
                $arrayPlanBloque = PlanesBloques::where('id_planes', $dato->id_planes)->get();



                $planCompletado = 1;

                if($arrayPlanBloque != null && $arrayPlanBloque->isNotEmpty()){
                    foreach ($arrayPlanBloque as $rec){ // cada bloque

                        // buscar el detalle de ese bloque
                        $arrayListado = PlanesBlockDetalle::where('id_planes_bloques', $rec->id)
                            ->where('visible', 1)
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
                }else{
                    $planCompletado = 0;
                }

                $dato->plancompletado = $planCompletado;
            }


            // obtener los planes completados
            $pilaIdPlanComplet = array();

            foreach ($arrayPlanUsuario as $item){
                if($item->plancompletado == 1){
                    array_push($pilaIdPlanComplet, $item->id_planes);
                }
            }




            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);


            $arrayPlanesUser = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->whereIn('id_planes', $pilaIdPlanComplet)
                ->paginate($limit, ['*'], 'page', $page);

            $hayinfo = 0;

            foreach ($arrayPlanesUser as $dato){
                $hayinfo = 1;
                $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                $dato->titulo = $titulosRaw['titulo'];
                $dato->subtitulo = $titulosRaw['subtitulo'];

                $infoP = Planes::where('id', $dato->id_planes)->first();
                $dato->imagen = $infoP->imagen;
                $dato->imagenportada = $infoP->imagenportada;
                $dato->idplan = $infoP->id;
            }


            // PROCESO DE ORDENAR POR TITULO

            // sortByDesc
            $sortedResult = $arrayPlanesUser->getCollection()->sortBy('titulo')->values();
            $arrayPlanesUser->setCollection($sortedResult);


            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayPlanesUser,
            ];

        }else{
            return ['success' => 99];
        }
    }





    private function retornoTextoPersonalizadoPlan($idiomaPlan, $idPlan)
    {
        if($info = PlanesBloquesTextos::where('id_planes_bloques', $idPlan)
            ->where('id_idioma_planes', $idiomaPlan)
            ->first()){

            return $info->titulo;

        }else{
            // si no encuentra sera por defecto español

            if($info = PlanesBloquesTextos::where('id_planes_bloques', $idPlan)
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






}
