<?php

namespace App\Http\Controllers\Api\Inicio;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarNotificacion;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\ComparteApp;
use App\Models\ComparteAppTextos;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\ImagenesDelDia;
use App\Models\InsigniasTextos;
use App\Models\InsigniasUsuarios;
use App\Models\InsigniasUsuariosConteo;
use App\Models\InsigniasUsuariosDetalle;
use App\Models\LecturaDia;
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
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use App\Models\PlanesUsuariosContinuar;
use App\Models\RachaAlta;
use App\Models\RachaDevocional;
use App\Models\RachaDias;
use App\Models\TipoInsignias;
use App\Models\UsuarioNotificaciones;
use App\Models\VideosHoy;
use App\Models\VideosTextos;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use DateTime;
use OneSignal;

class ApiInicioController extends Controller
{

    // devuelve todos los elementos bloque inicio
    public function infoBloqueInicioCompleto(Request $request){

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


            // horario actual del cliente segun zona horaria
            $zonaHorariaUsuario = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);

            // Array Final


            // TODOS LOS BLOQUES DEBERIAN MOSTRARSE

            DB::beginTransaction();
            try {

            // ************** BLOQUE DEVOCIONAL ******************

            $devo_haydevocional = 0; // Seguro para saber si hay devocional hoy
            $devo_idBlockDeta = 0; // Para redireccionar a sus preguntas
            $devo_preguntas = 1; // defecto para cuestionario nomas
            $devo_lecturaDia = "";

            $arrayPlanesSeleccionado = PlanesUsuarios::where('id_usuario', $userToken)
                ->select('id_planes')
                ->get();

                // si hay devocional para hoy segun zona horaria del usuario
                if($arrayL = DB::table('lectura_dia AS le')
                    ->join('planes_block_detalle AS pblock', 'le.id_planes_block_detalle', '=', 'pblock.id')
                    ->join('planes_bloques AS p', 'pblock.id_planes_bloques', '=', 'p.id')
                    ->select('p.fecha_inicio', 'pblock.id AS idblockdeta', 'p.id_planes')
                    ->whereIn('p.id_planes', $arrayPlanesSeleccionado) // solo si usuario ya tiene seleccioando
                    ->whereDate('p.fecha_inicio', '=', $zonaHorariaUsuario)
                    ->first()){

                    $devo_haydevocional = 1;
                    $devo_idBlockDeta = $arrayL->idblockdeta;

                    $devoDatos = $this->retornoTituloCuestionarioIdioma($arrayL->idblockdeta, $idiomaTextos);

                    $devo_lecturaDia = $devoDatos['textodia'];

                    // saver si tiene preguntas, y ver que esten activas al menos 1
                    $arrayBloqueP = BloquePreguntas::where('id_plan_block_detalle', $arrayL->idblockdeta)->get();

                    foreach ($arrayBloqueP as $bl){
                        if($bl->visible == 1){
                            $devo_preguntas = 2; // si mostrar fragment preguntas
                            break;
                        }
                    }
                }



            // ************** BLOQUE VIDEOS ******************

            $arrayFinalVideo = VideosHoy::orderBy('posicion', 'ASC')
                ->take(5)
                ->get();

            $video_hayvideoshoy = 0; // Seguro para saber si hay videos
            $video_mayor5 = 0; // cuando hay mas de 5 video redireccionamiento

            foreach ($arrayFinalVideo as $dato){
                $dato->titulo = $this->retornoTituloVideo($dato->id, $idiomaTextos);
            }

            $conteoVideo = VideosHoy::count();
            if($conteoVideo > 5){
                $video_mayor5 = 1;
            }

            if($arrayFinalVideo != null && $arrayFinalVideo->isNotEmpty()){
                $video_hayvideoshoy = 1;
            }




            // ************** BLOQUE IMAGENES DEL DIA ******************


            $arrayFinalImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')
                ->take(5)
                ->get();
            $imagenes_hayimageneshoy = 0; // Seguro para saber si hay imagenes del dia
            $imagenes_mayor5 = 0;


            $conteoImg = ImagenesDelDia::count();
            if($conteoImg > 5){
                $imagenes_mayor5 = 1;
            }

            if($arrayFinalImagenes != null && $arrayFinalImagenes->isNotEmpty()){
                $imagenes_hayimageneshoy = 1;
            }


            // ************** BLOQUE COMPARTE LA APLICACION ******************


            $comparte_arrayComparteApp = ComparteApp::where('id', 1)->first();
            $comparte_datosComparteApp = $this->retornoTituloCompartirAppIdioma($idiomaTextos);
            $comparte_titulo = $comparte_datosComparteApp['titulo'];
            $comparte_descripcion = $comparte_datosComparteApp['descripcion'];



            // ************** BLOQUE INSIGNIAS ******************

            // ordenar por fechas ganadas deberia ser mejor
            // solo visibles

                $insignia_arrayInsignias = DB::table('tipo_insignias AS t')
                    ->join('insignias_usuarios AS i', 'i.id_tipo_insignia', '=', 't.id')
                    ->where('i.id_usuario', $userToken->id)
                    ->select('t.visible', 'i.id_usuario', 'i.id_tipo_insignia', 'i.fecha')
                    ->take(5)
                    ->get();


            $insignia_hayInsignias = 0;
            $insignias_mayor5 = 0;


            if($insignia_arrayInsignias != null && $insignia_arrayInsignias->isNotEmpty()){
                $insignia_hayInsignias = 1;
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

                $hito_infoNivelVoy = 1;

                if($datoHitoNivel != null){
                    $dato->nivelvoy = $datoHitoNivel;
                    $hito_infoNivelVoy = $datoHitoNivel;
                }else{
                    $dato->nivelvoy = 1;
                }


                // ------ INFORMACION DEL HITO DE CADA INSIGNIA ------

                $hito_cuantoFaltan = 0; // Cuantos puntos me faltan para la siguiente nivel de esta insignia
                $hito_haySiguienteNivel = 0; // Saber si hay un nivel mas por superar


                // Obtener los niveles ya ganados para evitarlos
                $hito_arrayObtenidos = DB::table('insignias_usuarios_detalle AS indeta')
                    ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                    ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                    ->select('nil.id')
                    ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->get();

                $pilaIdYaGanados = array();

                foreach ($hito_arrayObtenidos as $item){
                    array_push($pilaIdYaGanados, $item->id);
                }

                // buscar el siguiente nivel que falta y cuanto me falta
                if($infoNivelSiguiente = NivelesInsignias::where('id_tipo_insignia', $dato->id_tipo_insignia)
                    ->whereNotIn('id', $pilaIdYaGanados)
                    ->where('nivel', '>', $hito_infoNivelVoy)
                    ->first()){

                    $hito_haySiguienteNivel = 1; // Si hay siguiente nivel
                    $hito_cuantoFaltan = $infoNivelSiguiente->nivel - $hito_infoNivelVoy;
                }

                $infoInsigniaP = TipoInsignias::where('id', $dato->id_tipo_insignia)->first();
                $dato->imageninsignia = $infoInsigniaP->imagen;

                $dato->hitohaynextlevel = $hito_haySiguienteNivel;
                $dato->hitocuantofalta = $hito_cuantoFaltan;


            }


            $arrayFinalInsignias = $insignia_arrayInsignias->sortBy('titulo')->values();


            $conteoInsignias = InsigniasUsuarios::where('id_usuario', $userToken->id)->count();

            if ($conteoInsignias > 5) {
                $insignias_mayor5 = 1;
            }


            // GUARDAR REGISTRO QUE VIO LA APLICACION
            if(RachaDias::where('id_usuario', $userToken->id)
                ->whereDate('fecha', $zonaHorariaUsuario)->first()){
                // no guardar
            }else{
                $nuevoDia = new RachaDias();
                $nuevoDia->id_usuario = $userToken->id;
                $nuevoDia->fecha = $zonaHorariaUsuario;
                $nuevoDia->save();
            }


            //******* NIVEL DE RACHA  //***********

            $infoTotalRachas = $this->retornoInformacionRacha($userToken);

            // guardar modificaciones
            DB::commit();

            return ['success' => 1,

                'videomayor5' => $video_mayor5,
                'imagenesmayor5' => $imagenes_mayor5,
                'insigniasmayor5' => $insignias_mayor5,

                'devohaydevocional' => $devo_haydevocional,
                'devoidblockdeta' => $devo_idBlockDeta,

                'videohayvideos' => $video_hayvideoshoy,

                'imageneshayhoy' => $imagenes_hayimageneshoy,

                'comparteappimagen' => $comparte_arrayComparteApp->imagen,
                'comparteapptitulo' => $comparte_titulo,
                'comparteappdescrip' => $comparte_descripcion,

                'insigniashay' => $insignia_hayInsignias,

                'devopreguntas' => $devo_preguntas,


                'arrayracha' => [$infoTotalRachas], // se mete en llaves
                'arrayfinalvideo' => $arrayFinalVideo,
                'arrayfinalimagenes' => $arrayFinalImagenes,
                'arrayfinalinsignias' => $arrayFinalInsignias,

                'devocuestionario' => $devo_lecturaDia

                ];


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



    private function retornoInformacionRacha($userToken){

        $fechaFormatHorariaCarbon = $this->retornoZonaHorariaDepaCarbonNow($userToken->id_iglesia);
        $anioActual = $fechaFormatHorariaCarbon->year;

        // LOS DIAS EN LA APLICACION ESTE ANIO
        $diasAppEsteAnio = RachaDias::where('id_usuario', $userToken->id)
            ->whereYear('fecha', $anioActual)
            ->count();

        // EL USUARIO CUANDO SE REGISTRA SE CREA EL RACHA ALTA


        $infoRachaAlta = RachaAlta::where('id_usuarios', $userToken->id)->first();


        $arrayRachaUserDevocional = RachaDevocional::where('id_usuario', $userToken->id)
            ->where('fecha', '<=', $fechaFormatHorariaCarbon)
            ->orderBy('fecha', 'DESC')
            ->get();

        $fechaActualH = $this->retornoZonaHorariaUsuarioFormatFecha($userToken->id_iglesia);
        $fechaActual = Carbon::parse($fechaActualH);

        $devocionalConsecutivos = 0;


        // ESTAMOS CONTANDO LOS DIAS SEGUIDOS UNICAMENTE
        foreach ($arrayRachaUserDevocional as $dato) {

            $fechaObjeto = Carbon::parse($dato->fecha);

            // si es fecha actual, se sumara 1
            if ($fechaObjeto->equalTo($fechaActual)) {
                $devocionalConsecutivos++;
                continue;
            }

            // Verificar si la fecha en el bucle es un día antes de la fecha actual
            if ($fechaObjeto->equalTo($fechaActual->copy()->subDay())) {
                // Incrementar el contador de devocional consecutivos
                $devocionalConsecutivos++;
                // Actualizar la fecha actual para la próxima iteración
                $fechaActual = $fechaObjeto;
            } else {
                // si estra aqui, la cadena de fechas se rompe
                break;
            }
        }

        $miRachaAlta = $infoRachaAlta->contador;

        // actualizar rachaAlta si requiere
        if($devocionalConsecutivos > $infoRachaAlta->contador){
            $miRachaAlta = $devocionalConsecutivos;

            // se debe actualizar
            RachaAlta::where('id_usuarios', $userToken->id)
                ->update([
                    'contador' => $devocionalConsecutivos,
                ]);
        }


        // VER QUE DIAS HIZO EL DEVOCIONAL


        $diaDomingo = 0;
        $diaLunes = 0;
        $diaMartes = 0;
        $diaMiercoles = 0;
        $diaJueves = 0;
        $diaViernes = 0;
        $diaSabado = 0;


        // obtener los dias de estas fechas seguidas
        $arrayFechaDias = RachaDevocional::where('id_usuario', $userToken->id)
            ->where('fecha', '<=', $fechaFormatHorariaCarbon)
            ->orderBy('fecha', 'DESC')
            ->take(7) // por seguridad nomas
            ->get();

        foreach ($arrayFechaDias as $dato){

            if(Carbon::parse($dato->fecha)->isMonday()){
                $diaLunes = 1;
            }

            if(Carbon::parse($dato->fecha)->isTuesday()){
                $diaMartes = 1;
            }

            if(Carbon::parse($dato->fecha)->isWednesday()){
                $diaMiercoles = 1;
            }

            if(Carbon::parse($dato->fecha)->isThursday()){
                $diaJueves = 1;
            }

            if(Carbon::parse($dato->fecha)->isFriday()){
                $diaViernes = 1;
            }

            if(Carbon::parse($dato->fecha)->isSaturday()){
                $diaSabado = 1;
            }

            if(Carbon::parse($dato->fecha)->isSunday()){
                $diaDomingo = 1;
            }
        }

        return ['diasesteanio' => $diasAppEsteAnio,
            'diasconcecutivos' => $devocionalConsecutivos,
            'nivelrachaalta' => $miRachaAlta,
            'domingo' => $diaDomingo,
            'lunes' => $diaLunes,
            'martes' => $diaMartes,
            'miercoles' => $diaMiercoles,
            'jueves' => $diaJueves,
            'viernes' => $diaViernes,
            'sabado' => $diaSabado];
    }







    public function listadoTodosLosVideos(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
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

            $arrayVideos = VideosHoy::orderBy('posicion', 'ASC')->get();

            foreach ($arrayVideos as $dato){

                $info = $this->retornoTituloVideo($dato->id, $idiomaTextos);
                $dato->titulo = $info;
            }

            return ['success' => 1,
                'arrayfinalvideo' => $arrayVideos];

        }else{
            return ['success' => 99];
        }
    }



    public function listadoTodosLasImagenes(Request $request)
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

            $arrayImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')->get();

            return ['success' => 1,
                'arrayfinalimagenes' => $arrayImagenes];

        }else{
            return ['success' => 99];
        }
    }


    public function listadoTodosLasInsignias(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
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

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            $insignia_arrayInsignias = InsigniasUsuarios::where('id_usuario', $userToken->id)->get();

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

                $hito_infoNivelVoy = 0;

                if($datoHitoNivel != null){
                    $dato->nivelvoy = $datoHitoNivel;
                    $hito_infoNivelVoy = $datoHitoNivel;
                }else{
                    $dato->nivelvoy = 1;
                }




                // ------ INFORMACION DEL HITO DE CADA INSIGNIA ------

                $hito_cuantoFaltan = 0; // Cuantos puntos me faltan para la siguiente nivel de esta insignia
                $hito_haySiguienteNivel = 0; // Saber si hay un nivel mas por superar


                // Obtener los niveles ya ganados para evitarlos
                $hito_arrayObtenidos = DB::table('insignias_usuarios_detalle AS indeta')
                    ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                    ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                    ->select('nil.id')
                    ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->get();

                $pilaIdYaGanados = array();

                foreach ($hito_arrayObtenidos as $item){
                    array_push($pilaIdYaGanados, $item->id);
                }

                // buscar el siguiente nivel que falta y cuanto me falta
                if($infoNivelSiguiente = NivelesInsignias::where('id_tipo_insignia', $dato->id_tipo_insignia)
                    ->whereNotIn('id', $pilaIdYaGanados)
                    ->where('nivel', '>', $hito_infoNivelVoy)
                    ->first()){

                    $hito_haySiguienteNivel = 1; // Si hay siguiente nivel
                    $hito_cuantoFaltan = $infoNivelSiguiente->nivel - $hito_infoNivelVoy;
                }

                $infoInsigniaP = TipoInsignias::where('id', $dato->id_tipo_insignia)->first();
                $dato->imageninsignia = $infoInsigniaP->imagen;

                $dato->hitohaynextlevel = $hito_haySiguienteNivel;
                $dato->hitocuantofalta = $hito_cuantoFaltan;
            }

            $arrayFinalInsignias = $insignia_arrayInsignias->sortBy('titulo')->values();

            return ['success' => 1,
                'arrayfinalinsignias' => $arrayFinalInsignias];

        }else{
            return ['success' => 99];
        }
    }



    public function informacionInsigniaIndividual(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
            'idinsignia' => 'required'
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

            $infoInsignia = TipoInsignias::where('id', $request->idinsignia)->first();

            $infoTitulos = $this->retornoTituloInsigniasAppIdioma($infoInsignia->id, $idiomaTextos);
            $tituloInsignia = $infoTitulos['titulo'];
            $descripcionInsignia = $infoTitulos['descripcion'];


            // Conocer que nivel voy (ejemplo devuelve 5)
            $datoHitoNivel = DB::table('insignias_usuarios_detalle AS indeta')
                ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                ->select('nil.nivel', 'nil.id AS idnivelinsignia')
                ->where('nil.id_tipo_insignia', $infoInsignia->id)
                ->max('nil.nivel');

            $hito_infoNivelVoy = 1;

            if($datoHitoNivel != null){
                $hito_infoNivelVoy = $datoHitoNivel;
            }



            // ------ INFORMACION DEL HITO DE CADA INSIGNIA ------

            $hito_cuantoFaltan = 0; // Cuantos puntos me faltan para la siguiente nivel de esta insignia
            $hito_haySiguienteNivel = 0; // Saber si hay un nivel mas por superar


            // Obtener los niveles ya ganados para evitarlos
            $hito_arrayObtenidos = DB::table('insignias_usuarios_detalle AS indeta')
                ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                ->select('nil.id', 'indeta.fecha', 'nil.nivel')
                ->where('nil.id_tipo_insignia', $infoInsignia->id)
                ->orderBy('indeta.fecha', 'DESC')
                ->get();

            // obteniendo fecha cuando se gano el hito
            foreach ($hito_arrayObtenidos as $dato){
                $fecha = date("d-m-Y", strtotime($dato->fecha));

                // como traigo idioma, necesito mostrar referencia
                $infoTexto = $this->retornoMensajeHito($idiomaTextos);

                $dato->hitotexto1 = $infoTexto['texto1'];
                $dato->hitotexto2 = $infoTexto['texto2'];
                $dato->fechaFormat = $fecha;
            }

            $cualNextLevel = 0;
            // buscar el siguiente nivel que falta y cuanto me falta
            if($infoNivelSiguiente = NivelesInsignias::where('id_tipo_insignia', $infoInsignia->id)
                ->where('nivel', '>', $hito_infoNivelVoy)
                ->first()){

                $infoConteo = InsigniasUsuariosConteo::where('id_tipo_insignia', $request->idinsignia)->first();

                $cualNextLevel = $infoNivelSiguiente->nivel;
                $hito_haySiguienteNivel = 1; // Si hay siguiente nivel
                $hito_cuantoFaltan = $infoNivelSiguiente->nivel - $infoConteo->conteo;
            }

            $arrayHitoOrdenado = $hito_arrayObtenidos->sortByDesc('nivel')->values();


            $textoFalta = $this->retornoMensajeHito($idiomaTextos);


            return ['success' => 1,
                'titulo' => $tituloInsignia,
                'descripcion' => $descripcionInsignia,
                'imagen' => $infoInsignia->imagen,
                'nivelvoy' => $hito_infoNivelVoy,
                'hitocuantofalta' => $hito_cuantoFaltan,
                'hitohaynextlevel' => $hito_haySiguienteNivel,
                'cualnextlevel' => $cualNextLevel,
                'textofalta' => $textoFalta['texto2'],
                'hitoarray' => $arrayHitoOrdenado
                ];

        }else{
            return ['success' => 99];
        }
    }

    private function retornoMensajeHito($idiomaPlan){

        if($idiomaPlan == 1){ // espanol

            return ['texto1' => "Completado el:",
                'texto2' => "Falta"];

        }else if($idiomaPlan == 2){ // ingles
            // si no encuentra sera por defecto español

            return ['texto1' => "Completed on:",
                'texto2' => "remaining"];
        }else{
            return ['texto1' => "Completado el:",
                    'texto2' => "Falta"];
        }
    }




    // actualizar planes usuarios continuar, esta tabla solo tiene 1 registro usuario
    private function retornoActualizarPlanUsuarioContinuar($iduser, $idplan)
    {
        if($idPlanUser = PlanesUsuariosContinuar::where('id_usuarios', $iduser)
            ->first()){
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










    // RETORNO TITULO VIDEOS
    private function retornoTituloVideo($idvideohoy, $idiomaTexto){

        if($infoTexto = VideosTextos::where('id_idioma_planes', $idiomaTexto)
            ->where('id_videos_hoy', $idvideohoy)
            ->first()){

            return $infoTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoTexto = VideosTextos::where('id_idioma_planes', 1)->first();
            return $infoTexto->titulo;
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

    // RETORNO TEXTOS PARA LOS TITULOS PARA COMPARTIR APP
    private function retornoTituloCompartirAppIdioma($idiomaTexto){

        // como solo hay 1 registro, solo buscar el primer idioma que coincida
        if($infoTexto = ComparteAppTextos::where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return ['titulo' => $infoTexto->texto_1,
                    'descripcion' => $infoTexto->texto_2];

        }else{
            // si no encuentra sera por defecto español

            $infoTexto = ComparteAppTextos::where('id_idioma_planes', 1)->first();

            return ['titulo' => $infoTexto->texto_1,
                'descripcion' => $infoTexto->texto_2];
        }
    }

    // RETORNO TODA DESCRIPCION DE UN CUESTIONARIO
    private function retornoTituloCuestionarioIdioma($idBlockDeta, $idiomaTexto){

        if($infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return ['texto' => $infoTituloTexto->texto, 'textodia' => $infoTituloTexto->texto_dia];

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['texto' => $infoTituloTexto->texto, 'textodia' => $infoTituloTexto->texto_dia];
        }
    }


    // COMO IDIOMATEXTOS DEVUELVE 0 POR DEFECTO, Y EL ID 1 ES MINIMO EN LA BASE DE DATOS
    private function reseteoIdiomaTextos($idiomatextos)
    {
        if($idiomatextos == 0){
            $idiomatextos = 1;
        }

        return $idiomatextos;
    }


    // RETORNO HORARIO ACTUAL DEL USUARIO SEGUN ZONA HORARIA -> DEVUELVE SOLO FECHA
    private function retornoZonaHorariaUsuarioFormatFecha($idIglesia){
        $infoIglesia = Iglesias::where('id', $idIglesia)->first();
        $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();
        $zonaHoraria = $infoZonaHoraria->zona;

        // horario actual del cliente segun zona horaria

        return Carbon::now($zonaHoraria)->format('Y-m-d');
    }


    // RETORNO DE ZONA HORARIA SEGUN DEPARTAMENTO
    private function retornoZonaHorariaDepaCarbonNow($idIglesia)
    {
        $infoIglesia = Iglesias::where('id', $idIglesia)->first();
        $infoDepartamento = Departamentos::where('id', $infoIglesia->id_departamento)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoDepartamento->id_zona_horaria)->first();
        return Carbon::now($infoZonaHoraria->zona);
    }




    public function listadoInsigniasFaltantesPorGanar(Request $request)
    {
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

            $idiomaTexto = $request->idiomaplan;

            $insigniasGanadas = InsigniasUsuarios::where('id_usuario', $userToken->id)
                ->select('id_tipo_insignia')
                ->get();

            $listado = TipoInsignias::whereNotIn('id', $insigniasGanadas)
                ->where('visible', 1)
                ->get();

            $hayinfo = 0;
            if($listado != null && $listado->isNotEmpty()){
                $hayinfo = 1;
            }

            foreach ($listado as $dato){

                $datosRaw = $this->retornoTituloInsigniasAppIdioma($dato->id, $idiomaTexto);
                $titulo = $datosRaw['titulo'];
                $descripcion = $datosRaw['descripcion'];

                $dato->titulo = $titulo;
                $dato->descripcion = $descripcion;
            }

            $arrayFinalInsignias = $listado->sortBy('titulo')->values();


            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $arrayFinalInsignias];
        }else{
            return ['success' => 99];
        }
    }


    // COMPARTIR APLICACION -> ONE SIGNAL
    public function compartirAplicacion(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return ['success' => 0,'msj' => "validación incorrecta" ]; }

        $tokenApi = $request->header('Authorization');


        if ($userToken = JWTAuth::user($tokenApi)) {

            $idTipoInsignia = 1; // COMPARTIR APLICACION


            DB::beginTransaction();
            try {

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


                // COMPARTIR APLICACION
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

                                // SUBIO NIVEL HITO - COMPARTIR APLICACION
                                $notiHistorial = new NotificacionUsuario();
                                $notiHistorial->id_usuario = $userToken->id;
                                $notiHistorial->tipo_tipo_notificacion = 2;
                                $notiHistorial->fecha = $fechaCarbon;
                                $notiHistorial->save();

                                break;
                            }
                        }
                    }

                    if($enviarNoti){

                        if($hayIdOne){
                            // SUBI DE NIVEL INSIGNIA COMPARTIR APP
                            $datosRaw = $this->retornoTitulosNotificaciones(2, $idiomaTexto);
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
                    $nuevaInsignia->id_tipo_insignia = $idTipoInsignia; // compartir App
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

                    if($hayIdOne){
                        // GANE INSIGNIA COMPARTIR APP
                        $datosRaw = $this->retornoTitulosNotificaciones(1, $idiomaTexto);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];



                        // Guardar Historial Notificacion Usuario
                        $notiHistorial = new NotificacionUsuario();
                        $notiHistorial->id_usuario = $userToken->id;
                        $notiHistorial->tipo_tipo_notificacion = 1; // POR GANAR PRIMERA INSIGNIA COMPARTIR APLICACION
                        $notiHistorial->fecha = $fechaCarbon;
                        $notiHistorial->save();



                        // como es primera vez, se necesita enviar notificacion
                        dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                    }
                }

                DB::commit();
                return ['success' => 1];
            }catch(\Throwable $e) {
                DB::rollback();
                Log::info("error: " . $e);
                return ['success' => 99];
            }
        }else{
            return ['success' => 2];
        }
    }







    // COMPARTIR DEVOCIONAL -> ONE SIGNAL
    public function compartirDevocional(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) { return ['success' => 0,'msj' => "validación incorrecta" ]; }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {


            // GUARDAR PUNTO DE COMPARTIR APLICACION

            DB::beginTransaction();
            try {


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



                    // PRIMERA VEZ - INSIGNIA COMPARTIR DEVOCIONAL
                    $notiHistorial = new NotificacionUsuario();
                    $notiHistorial->id_usuario = $userToken->id;
                    $notiHistorial->tipo_tipo_notificacion = 3;
                    $notiHistorial->fecha = $fechaCarbon;
                    $notiHistorial->save();



                    if($hayIdOne){
                        // GANE INSIGNIA COMPARTIR DEVOCIONAL
                        $datosRaw = $this->retornoTitulosNotificaciones(3, $idiomaTexto);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];

                        // como es primera vez, se necesita enviar notificacion
                        dispatch(new EnviarNotificacion($pilaOneSignal, $tiNo, $desNo));
                    }
                }

                DB::commit();
                return ['success' => 1];
            }catch(\Throwable $e) {
                DB::rollback();
                Log::info("error: " . $e);
                return ['success' => 99];
            }
        }else{
            return ['success' => 2];
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


}
