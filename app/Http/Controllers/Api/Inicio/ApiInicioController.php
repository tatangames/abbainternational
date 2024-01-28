<?php

namespace App\Http\Controllers\Api\Inicio;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\ComparteApp;
use App\Models\ComparteAppTextos;
use App\Models\ContenedorInicio;
use App\Models\Iglesias;
use App\Models\ImagenesDelDia;
use App\Models\InsigniasTextos;
use App\Models\InsigniasUsuarios;
use App\Models\InsigniasUsuariosDetalle;
use App\Models\LecturaDia;
use App\Models\NivelesInsignias;
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
use App\Models\RachaUsuario;
use App\Models\TipoInsignias;
use App\Models\VideosHoy;
use App\Models\VideosTextos;
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

        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            // horario actual del cliente segun zona horaria
            $zonaHorariaUsuario = $this->retornoZonaHorariaUsuario($userToken->id_iglesia);

            // Array Final

            // Si se mostrara o no el array final
            $mostrarFinalDevocional = 0;
            $mostrarFinalVideo = 0;
            $mostrarFinalImagenes = 0;
            $mostrarFinalComparteApp = 0;
            $mostrarFinalInsignias = 0;


            // Conocer las posiciones que tendra cada bloque
            $infoContenedorInicio = ContenedorInicio::all();
            foreach ($infoContenedorInicio as $dato){
                if($dato->id == 1){ // Devocional
                    $mostrarFinalDevocional = $dato->visible;
                }else if($dato->id == 2){ // Videos
                    $mostrarFinalVideo = $dato->visible;
                }else if($dato->id == 3){ // Imagenes
                    $mostrarFinalImagenes = $dato->visible;
                }else if($dato->id == 4){ // Comparte app
                    $mostrarFinalComparteApp = $dato->visible;
                }else if($dato->id == 5){ // Insignias
                    $mostrarFinalInsignias = $dato->visible;
                }
            }


            // ************** BLOQUE DEVOCIONAL ******************

            $devo_haydevocional = 0; // Seguro para saber si hay devocional hoy
            $devo_cuestionario = "";
            $devo_idBlockDeta = 0; // Para redireccionar a sus preguntas

            if($mostrarFinalDevocional == 1){
                // si hay devocional para hoy segun zona horaria del usuario
                if($arrayL = DB::table('lectura_dia AS le')
                    ->join('planes_block_detalle AS pblock', 'le.id_planes_block_detalle', '=', 'pblock.id')
                    ->join('planes_bloques AS p', 'pblock.id_planes_bloques', '=', 'p.id')
                    ->select('p.fecha_inicio', 'pblock.id AS idblockdeta')
                    ->whereDate('p.fecha_inicio', '=', $zonaHorariaUsuario)
                    ->first()){

                    $devo_haydevocional = 1;
                    $devo_idBlockDeta = $arrayL->idblockdeta;
                    $devo_cuestionario = $this->retornoTituloCuestionarioIdioma($arrayL->idblockdeta, $idiomaTextos);
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


            $insignia_arrayInsignias = InsigniasUsuarios::where('id_usuario', $userToken->id)
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
                $hito_infoNivelVoy = DB::table('insignias_usuarios_detalle AS indeta')
                    ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                    ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                    ->select('nil.nivel', 'nil.id AS idnivelinsignia')
                    ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->max('nil.nivel');

                $dato->nivelvoy = $hito_infoNivelVoy;


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

            $arrayFinalInsignias = $insignia_arrayInsignias->sortBy('titulo');

            $conteoInsignias = InsigniasUsuarios::where('id_usuario', $userToken->id)->count();

            if ($conteoInsignias > 5) {
                $insignias_mayor5 = 1;
            }

            return ['success' => 1,
                'mostrarbloquedevocional' => $mostrarFinalDevocional,
                'mostrarbloquevideo' => $mostrarFinalVideo,
                'mostrarbloqueimagenes' => $mostrarFinalImagenes,
                'mostrarbloquecomparte' => $mostrarFinalComparteApp,
                'mostrarbloqueinsignias' => $mostrarFinalInsignias,

                'videomayor5' => $video_mayor5,
                'imagenesmayor5' => $imagenes_mayor5,
                'insigniasmayor5' => $insignias_mayor5,

                'devohaydevocional' => $devo_haydevocional,
                'devocuestionario' => $devo_cuestionario,
                'devoidblockdeta' => $devo_idBlockDeta,

                'videohayvideos' => $video_hayvideoshoy,

                'imageneshayhoy' => $imagenes_hayimageneshoy,

                'comparteappimagen' => $comparte_arrayComparteApp->imagen,
                'comparteapptitulo' => $comparte_titulo,
                'comparteappdescrip' => $comparte_descripcion,

                'insigniashay' => $insignia_hayInsignias,


                'arrayfinalvideo' => $arrayFinalVideo,
                'arrayfinalimagenes' => $arrayFinalImagenes,
                'arrayfinalinsignias' => $arrayFinalInsignias,



                ];



        }
        else{
            return ['success' => 99];
        }
    }



    public function infoPlanSoloVista(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'idblockdeta' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        if($infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first()){

            $infoPlanBloque = PlanesBloques::where('id', $infoBlockDeta->id_planes_bloques)->first();
            $infoPlan = Planes::where('id', $infoPlanBloque->id_planes)->first();


            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            $titulo = "";
            $subtitulo = null;
            $descripcion = null;

            if($infoPlanTextos = PlanesTextos::where('id_planes', $infoPlan->id)
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




    public function preguntasInicioGuardarActualizar(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required'
        );

        // Map listado

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $infoIglesia = Iglesias::where('id', $userToken->id_iglesia)->first();
            $infoZonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();
            $zonaHoraria = $infoZonaHoraria->zona;

            DB::beginTransaction();

            try {

                if($infoBlockDeta = PlanesBlockDetalle::where('id', $request->idblockdeta)->first()) {

                    $infoPlanBloque = PlanesBloques::where('id', $infoBlockDeta->id_planes_bloques)->first();
                    $infoPlan = Planes::where('id', $infoPlanBloque->id_planes)->first();

                    // Verificar si usuario tiene registrado el plan, sino registrar
                    if(PlanesUsuarios::where('id_usuario', $userToken->id)
                        ->where('id_planes')->first()){
                        // no hacer nada porque esta registrado
                    }else{
                        // vincular el plan al usuario
                        $nuevo = new PlanesUsuarios();
                        $nuevo->id_usuario = $userToken->id;
                        $nuevo->id_planes = $infoPlan->id;
                        $nuevo->fecha = Carbon::now($zonaHoraria);
                        $nuevo->save();
                    }


                    if ($request->has('idpregunta')) {

                        foreach ($request->idpregunta as $clave => $valor) {

                            if(BloquePreguntasUsuarios::where('id_bloque_preguntas', $clave)
                                ->where('id_usuarios', $userToken->id)->first()){

                                // actualizar porque ya estan registradas
                                BloquePreguntasUsuarios::where('id', $clave)
                                    ->update([
                                        'texto' => $valor['txtpregunta'],
                                        'fecha_actualizo' => Carbon::now($zonaHoraria)
                                    ]);

                            }else{
                                $pregunta = new BloquePreguntasUsuarios();
                                $pregunta->id_bloque_preguntas = $clave;
                                $pregunta->id_usuarios = $userToken->id;
                                $pregunta->texto = $valor['txtpregunta'];
                                $pregunta->fecha = Carbon::now($zonaHoraria);
                                $pregunta->fecha_actualizo = null;
                                $pregunta->save();
                            }
                        }
                    }


                    // colocar plan continuar por defecto
                    $this->retornoActualizarPlanUsuarioContinuar($userToken->id, $infoPlan->id);


                    DB::commit();
                    return ['success' => 1];
                }else{
                    return ['success' => 99];
                }
            }catch(\Throwable $e){
                DB::rollback();
                Log::info("error: " . $e);
                return ['success' => 99];
            }

        }else{
            return ['success' => 99];
        }
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

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

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



    private function svewrf(){

        // ------- CONOCER LA RACHA QUE LLEVO  -----------

        /*$arrayRachaUser = RachaUsuario::where('id_usuarios', $userToken->id)
            ->orderBy('fecha', 'DESC')
            ->get();

        $fechaActualH = $this->retornoZonaHorariaUsuarioFormatFecha($userToken->id_iglesia);
        $fechaActual = Carbon::parse($fechaActualH);
        $diasConsecutivos = 0;

        $pilaIdFechas = array();


        foreach ($arrayRachaUser as $dato) {
            // Convertir la cadena de fecha a un objeto DateTime
            $fechaObjeto = Carbon::parse($dato->fecha);

            array_push($pilaIdFechas, $dato->id);

            // Verificar si la fecha actual es igual a la fecha en el bucle

            if ($fechaObjeto->equalTo($fechaActual)) {

                // Ignorar la fecha actual y continuar con la próxima iteración
                continue;
            }

            // Verificar si la fecha en el bucle es un día antes de la fecha actual
            if ($fechaObjeto->equalTo($fechaActual->copy()->subDay())) {
                // Incrementar el contador de días consecutivos
                $diasConsecutivos++;
                // Actualizar la fecha actual para la próxima iteración
                $fechaActual = $fechaObjeto;
            } else {
                // Si no es un día antes, salir del bucle
                break;
            }
        }*/


    }






    // RETORNO TITULO VIDEOS
    private function retornoTituloVideo($idvideohoy, $idiomaTexto){

        if($infoTexto = VideosTextos::where('id_idioma_planes', $idiomaTexto)
            ->where('id_videos_hoy', $idvideohoy)
            ->first()){

            return $infoTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoTexto = VideosTextos::where('id_idioma_planes', 1)
                ->first();

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

            return $infoTituloTexto->texto;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoTituloTexto->texto;
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
        $infoZonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();
        $zonaHoraria = $infoZonaHoraria->zona;

        // horario actual del cliente segun zona horaria

        return Carbon::now($zonaHoraria)->format('Y-m-d');
    }

    // RETORNO HORARIO ACTUAL DEL USUARIO SEGUN ZONA HORARIA
    private function retornoZonaHorariaUsuario($idIglesia){
        $infoIglesia = Iglesias::where('id', $idIglesia)->first();
        $infoZonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();
        $zonaHoraria = $infoZonaHoraria->zona;

        // horario actual del cliente segun zona horaria

        return Carbon::now($zonaHoraria);
    }
}
