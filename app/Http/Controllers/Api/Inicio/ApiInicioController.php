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
use App\Models\PlanesBlockDetaUsuarioTotal;
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
use App\Models\Usuarios;
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

       // idonesignal

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

                // actualizar primero el idioma
                Usuarios::where('id', $userToken->id)
                    ->update([
                        'idioma_noti' => $idiomaTextos,
                    ]);


                $idOneSignal = $request->idonesignal;

                if($idOneSignal != null){
                    if(strlen($idOneSignal) == 0){
                        // vacio no hacer nada
                    }else{

                        // actualizar nomas
                        Usuarios::where('id', $userToken->id)->update([
                            'onesignal' => $idOneSignal,
                        ]);
                    }
                }




            // ************** BLOQUE DEVOCIONAL ******************

            $devo_haydevocional = 0; // Seguro para saber si hay devocional hoy
            $devo_idBlockDeta = 0; // Para redireccionar a sus preguntas
            $devo_preguntas = 1; // defecto para cuestionario nomas
            $devo_lecturaDia = "";
            $devo_plan = 0;

            $arrayPlanesSeleccionado = PlanesUsuarios::where('id_usuario', $userToken->id)
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

                    // id plan
                    $devo_plan = $arrayL->id_planes;

                    $devo_haydevocional = 1;
                    $devo_idBlockDeta = $arrayL->idblockdeta;

                    $devoDatos = $this->retornoTituloCuestionarioIdioma($arrayL->idblockdeta, $idiomaTextos);

                    $devo_lecturaDia = $devoDatos['texto'] . $devoDatos['textodia'];


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


            if($idiomaTextos == 1){ // espanol
                $arrayFinalImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')
                    ->take(5)
                    ->get();
            }else{
                // ingles
                $arrayFinalImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')
                    ->take(5)
                    ->get();

                // SOBREESCRIBIENDO DATOS
                foreach ($arrayFinalImagenes as $dato){

                    $dato->imagen = $dato->imagen_ingles;
                }
            }





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


                $datoHitoNivel = DB::table('insignias_usuarios_detalle AS iud')
                    ->join('niveles_insignias AS ni', 'iud.id_niveles_insignias', '=', 'ni.id')
                    ->select('ni.nivel', 'ni.id AS idnivelinsignia', 'iud.id_usuarios')
                    ->where('ni.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->where('iud.id_usuarios', $userToken->id)
                    ->max('ni.nivel');


                if($datoHitoNivel != null){
                    $dato->nivelvoy = $datoHitoNivel;
                }else{
                    $dato->nivelvoy = 1;
                }

                $infoInsigniaP = TipoInsignias::where('id', $dato->id_tipo_insignia)->first();
                $dato->imageninsignia = $infoInsigniaP->imagen;
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

            $urlapple = "https://apps.apple.com/sv/app/mi-caminar-con-dios/id6480132659";


            // guardar modificaciones
            DB::commit();

            return ['success' => 1,

                'urlapple' => $urlapple,
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
                'devoplan' => $devo_plan,

                'devocuestionario' => $devo_lecturaDia,

                'arrayracha' => [$infoTotalRachas], // se mete en llaves
                'arrayfinalvideo' => $arrayFinalVideo,
                'arrayfinalimagenes' => $arrayFinalImagenes,
                'arrayfinalinsignias' => $arrayFinalInsignias,
                ];


            }catch(\Throwable $e){
                Log::info("error" . $e);
                DB::rollback();
                return ['success' => 99, 'error' => 'ee' . $e];
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


        // SERA SUMATORIA DE TODAS LAS VECES QUE ABRI LA APP.
        // PERO SOLO SUMARA SI LOS DIAS SEGUIDOS LLEGA A SUPERAR LA RACHA MAS ALTA

        $infoRachaAlta = RachaAlta::where('id_usuarios', $userToken->id)->first();

        $fechaActualH = $this->retornoZonaHorariaUsuarioFormatFecha($userToken->id_iglesia);


        $arrayFechaDevoPorSeguido = PlanesBlockDetaUsuarioTotal::where('id_usuario', $userToken->id)
            ->where('fecha', '<=', $fechaFormatHorariaCarbon)
            ->select('fecha')
            ->orderBy('fecha', 'DESC')
            ->groupBy('fecha')
            ->get();

        // Convertir cada fecha a una instancia de Carbon
        $fechas = $arrayFechaDevoPorSeguido->map(function ($item) {
            return Carbon::parse($item->fecha);
        });

        // Inicializar el contador
        $devocionalConsecutivos = 0;
        $puedeContar = true;

        // Verificar si hay fechas en el array
        if (!$fechas->isEmpty()) {
            // Comparar la primera fecha con la fecha actual
            if ($fechas->first()->isSameDay($fechaActualH)) {
                $devocionalConsecutivos++;
            } else {
                // Si la primera fecha no es igual a hoy, no tiene sentido continuar
                $puedeContar = false;
            }

            if($puedeContar){
                // Iterar a través de las fechas y comparar cada una con el día anterior
                for ($i = 1; $i < $fechas->count(); $i++) {
                    if ($fechas[$i]->isSameDay($fechas[$i - 1]->copy()->subDay())) {
                        $devocionalConsecutivos++;
                    } else {
                        break;
                    }
                }
            }
        }


        $miRachaAlta = $infoRachaAlta->contador;

        // ACTUALIZAR LA RACHA MAS ALTA
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


        // FILTRAR DE LA FECHA ACTUAL DEL SERVIDOR HACIA ATRAS
        // DESPUES SE DEBE OBTENER SOLO FECHA DE LA ACTUAL HACIA ATRAS

        $arrayFechaDevo = PlanesBlockDetaUsuarioTotal::where('id_usuario', $userToken->id)
            ->whereDate('fecha', '<=', $fechaFormatHorariaCarbon)
            ->orderBy('fecha', 'DESC')
            ->take(7)
            ->get();

        // dias que se restan
        $fechaModificable = Carbon::parse($fechaFormatHorariaCarbon)->format('y-m-d');

        $fechasEncontradas = [];

        // RECORRER CADA FECHA
        foreach ($arrayFechaDevo as $dato) {


            // por cada fecha, se debe iterar todoo de nuevo
            foreach ($arrayFechaDevo as $jj){
                $carbonFecha1 = Carbon::parse($jj->fecha);

                if ($carbonFecha1->equalTo($fechaModificable)) {
                    // encontro
                    if (!in_array($jj->fecha, $fechasEncontradas)) {
                        $fechasEncontradas[] = $jj->fecha;
                    }
                }
            }

            // restar
            $fechaModificable = Carbon::parse($fechaModificable)->subDay()->format('Y-m-d');
        }


        foreach ($fechasEncontradas as $dato){

            if(Carbon::parse($dato)->isMonday()){
                $diaLunes = 1;
            }

            if(Carbon::parse($dato)->isTuesday()){
                $diaMartes = 1;
            }

            if(Carbon::parse($dato)->isWednesday()){
                $diaMiercoles = 1;
            }

            if(Carbon::parse($dato)->isThursday()){
                $diaJueves = 1;
            }

            if(Carbon::parse($dato)->isFriday()){
                $diaViernes = 1;
            }

            if(Carbon::parse($dato)->isSaturday()){
                $diaSabado = 1;
            }

            if(Carbon::parse($dato)->isSunday()){
                $diaDomingo = 1;
            }
        }

        return ['diasesteanio' => $diasAppEsteAnio, // YAP
            'diasconcecutivos' => $devocionalConsecutivos, // POR DEVOCIONAl POR DIA SEGUIDO
            'nivelrachaalta' => $miRachaAlta, // RACHA MAS ALTAA
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

        // 'idiomaplan' => 'required',

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $request->idiomaplan;


            if($idiomaTextos != null){

                if($idiomaTextos == 1){ // espanol
                    $arrayImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')->get();
                }else{
                    // ingles
                    $arrayImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')->get();

                    // SOBREESCRIBIENDO DATOS
                    foreach ($arrayImagenes as $dato){

                        $dato->imagen = $dato->imagen_ingles;
                    }
                }
            }else{

                $arrayImagenes = ImagenesDelDia::orderBy('posicion', 'ASC')->get();
            }

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
                    ->where('indeta.id_usuarios', $userToken->id)
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
                ->where('indeta.id_usuarios', $userToken->id)
                ->max('nil.nivel');

            $hito_infoNivelVoy = 1;

            if($datoHitoNivel != null){
                $hito_infoNivelVoy = $datoHitoNivel;
            }


            // ------ INFORMACION DEL HITO DE CADA INSIGNIA ------

            // Obtener los niveles ya ganados para evitarlos
            $hito_arrayObtenidos = DB::table('insignias_usuarios_detalle AS indeta')
                ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                ->select('nil.id', 'indeta.fecha', 'nil.nivel')
                ->where('nil.id_tipo_insignia', $infoInsignia->id)
                ->where('indeta.id_usuarios', $userToken->id)
                ->orderBy('indeta.fecha', 'DESC')
                ->get();

            // obteniendo fecha cuando se gano el hito
            foreach ($hito_arrayObtenidos as $dato){
                $fecha = date("d-m-Y", strtotime($dato->fecha));

                // como traigo idioma, necesito mostrar referencia
                $infoTexto = $this->retornoMensajeHito($idiomaTextos);

                $dato->hitotexto1 = $infoTexto['texto1'];
                $dato->fechaFormat = $fecha;
            }


            $arrayHitoOrdenado = $hito_arrayObtenidos->sortByDesc('nivel')->values();


            $contadorActual = 0;
            if($infoContadorActual = InsigniasUsuariosConteo::where('id_usuarios', $userToken->id)
                ->where('id_tipo_insignia', $request->idinsignia)->first()){
                $contadorActual = $infoContadorActual->conteo;
            }

            return ['success' => 1,

                'titulo' => $tituloInsignia,
                'descripcion' => $descripcionInsignia,
                'imagen' => $infoInsignia->imagen,
                'nivelvoy' => $hito_infoNivelVoy,
                'contador' => $contadorActual,
                'hitoarray' => $arrayHitoOrdenado
                ];

        }else{
            return ['success' => 99];
        }
    }


    public function listadoNivelesDeInsignia(Request $request)
    {

        $rules = array(
            'idtipoinsignia' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $arrayNiveles = NivelesInsignias::where('id_tipo_insignia', $request->idtipoinsignia)->get();

        return ['success' => 1, 'listado' => $arrayNiveles];
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

            return ['texto' => $infoTituloTexto->titulo,
                'textodia' => $infoTituloTexto->titulo_dia];

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['texto' => $infoTituloTexto->titulo,
                'textodia' => $infoTituloTexto->titulo_dia];
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

            $unavez = true;

            foreach ($listado as $dato){

                $datosRaw = $this->retornoTituloInsigniasAppIdioma($dato->id, $idiomaTexto);
                $titulo = $datosRaw['titulo'];
                $descripcion = $datosRaw['descripcion'];

                $dato->titulo = $titulo;
                $dato->descripcion = $descripcion;

                $unavez = false;
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
                                $notiHistorial->id_tipo_notificacion = 2;
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
                        $notiHistorial->id_tipo_notificacion = 1; // POR GANAR PRIMERA INSIGNIA COMPARTIR APLICACION
                        $notiHistorial->fecha = $fechaCarbon;
                        $notiHistorial->save();


                        // como es primera vez, se necesita enviar notificacion
                        dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
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

                    // maximo nivel
                    $maxNiveles = NivelesInsignias::where('id_tipo_insignia', $idTipoInsignia)->max('nivel');

                    if($conteo <= $maxNiveles){

                        // solo actualizar conteo

                        InsigniasUsuariosConteo::where('id_tipo_insignia', $idTipoInsignia)
                            ->where('id_usuarios', $userToken->id)
                            ->update(['conteo' => $conteo]);

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
                    $notiHistorial->id_tipo_notificacion = 3;
                    $notiHistorial->fecha = $fechaCarbon;
                    $notiHistorial->save();



                    if($hayIdOne){
                        // GANE INSIGNIA COMPARTIR DEVOCIONAL
                        $datosRaw = $this->retornoTitulosNotificaciones(3, $idiomaTexto);
                        $tiNo = $datosRaw['titulo'];
                        $desNo = $datosRaw['descripcion'];

                        // como es primera vez, se necesita enviar notificacion
                        dispatch(new EnviarNotificacion($idOneSignalUsuario, $tiNo, $desNo));
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
