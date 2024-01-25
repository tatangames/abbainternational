<?php

namespace App\Http\Controllers\Api\Inicio;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\ComparteApp;
use App\Models\ComparteAppTextos;
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
use App\Models\VideosHoy;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Facebook\Facebook;
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



            $infoIglesia = Iglesias::where('id', $userToken->id_iglesia)->first();
            $infoZonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();
            $zonaHoraria = $infoZonaHoraria->zona;

            // horario actual del cliente segun zona horaria
            $horaServerUser = Carbon::now($zonaHoraria);


            // si hay un devocional para este dia
            $hayLecturaDia = 0;
            $arrayLecturaDia = null; // array del devocional
            $textoLecturaDia = "";
            // para saber redireccionar al usuario al cuestionario y sus preguntas
            $idBlockDeta = 0;

            // si hay devocional para hoy segun zona horaria del usuario
            if($arrayL = DB::table('lectura_dia AS le')
                ->join('planes_block_detalle AS pblock', 'le.id_planes_block_detalle', '=', 'pblock.id')
                ->join('planes_bloques AS p', 'pblock.id_planes_bloques', '=', 'p.id')
                ->select('p.fecha_inicio', 'pblock.id AS idblockdeta')
                ->whereDate('p.fecha_inicio', '=', Carbon::now($zonaHoraria))
                ->first()){
                $hayLecturaDia = 1;
                $arrayLecturaDia = $arrayL;

                $textoLecturaDia = $this->retornoTituloCuestionarioIdioma($arrayL->idblockdeta, $idiomaTextos);
            }


            // BLOQUE DE VIDEOS
            $arrayVideosHoy = VideosHoy::orderBy('posicion', 'ASC')->get();
            $hayvideoshoy = 0;

            if($arrayVideosHoy != null && $arrayVideosHoy->isNotEmpty()){
                $hayvideoshoy = 1;
            }


            // BLOQUE IMAGENES DEL DIA
            $arrayImagenesDIa = ImagenesDelDia::orderBy('posicion', 'ASC')->get();
            $hayimageneshoy = 0;
            if($arrayImagenesDIa != null && $arrayImagenesDIa->isNotEmpty()){
                $hayimageneshoy = 1;
            }


            // BLOQUE COMPARTE APP
            // solo habra 1 registro
            $arrayComparteApp = ComparteApp::where('id', 1)->first();
            $datosComparteApp = $this->retornoTituloCompartirAppIdioma($idiomaTextos);
            $tituloCompartir = $datosComparteApp['titulo'];
            $descripcionCompartir = $datosComparteApp['descripcion'];


            // BLOQUE INSIGNIAS
            $arrayInsignias = InsigniasUsuarios::where('id_usuario', $userToken->id)->get();
            $hayInsignias = 0;


            $fb = new Facebook([
                'app_id' => config('services.facebook.app_id'),
                'app_secret' => config('services.facebook.app_secret'),
                'default_graph_version' => 'v19.0',
            ]);

            $accessToken = "EAAFnZADyB608BO75R4UzVlt1BLuUqflnVJZARSC6fjVQZBQzSW4A9ZCQgl5CaodwheVTBlISoauS4znncZCkZADFvWZBt6I99G4tI9drKeR3T3O3ytgWDlZBPusudT8CTKCD1jVZCz5Fq4FVcpIgZBjm6UKZA7zyafsAZAM57VzpuJuZBollPvacSxiF9jfKqFmVdXjhGc3f5DML8";

            try {
                $response = $fb->get('/' . config('services.facebook.page_id') . '?fields=name', $accessToken);
                //$pageData = $response->getGraphPage();

                // Obtener el nombre de la página
                $pageName = 4;

                return response()->json(['page_name' => $pageName]);

            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                return response()->json(['error' => 'Error de la API de Facebook: ' . $e->getMessage()], 500);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                return response()->json(['error' => 'Error del SDK de Facebook: ' . $e->getMessage()], 500);
            }


            foreach ($arrayInsignias as $dato){
                $hayInsignias = 1;
                $infoTitulos = $this->retornoTituloInsigniasAppIdioma($dato->id_tipo_insignia, $idiomaTextos);
                $dato->titulo = $infoTitulos['titulo'];
                $dato->descripcion = $infoTitulos['descripcion'];


                // conocer que nivel voy (ejemplo devuelve 5)
                $infoNivelVoy = DB::table('insignias_usuarios_detalle AS indeta')
                    ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                    ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                    ->select('nil.nivel', 'nil.id AS idnivelinsignia')
                    ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->max('nil.nivel');

                $dato->nivelvoy = $infoNivelVoy;





                // saber cuantos puntos me faltan para el siguiente Hito
                //$cuantoFaltan = 0;

                // obtener los ya ganados para evitarlos
                /*$arrayObtenidos = DB::table('insignias_usuarios_detalle AS indeta')
                    ->join('niveles_insignias AS nil', 'indeta.id_niveles_insignias', '=', 'nil.id')
                    ->join('tipo_insignias AS tipo', 'nil.id_tipo_insignia', '=', 'tipo.id')
                    ->select('nil.id')
                    ->where('nil.id_tipo_insignia', $dato->id_tipo_insignia)
                    ->get();

                $pilaIdo = array();

                foreach ($arrayObtenidos as $item){
                    array_push($pilaIdo, $item->id);
                }*/



                // buscar el siguiente nivel que falta y cuanto me falta
                /*if($infon = NivelesInsignias::where('id_tipo_insignia', $dato->id_tipo_insignia)
                    ->whereNotIn('id', $pilaIdo)
                    ->where('nivel', '>', $infoNivelVoy)
                    ->first()){

                    $falta = $infon->nivel - $infoNivelVoy;

                    return "siguiente nivel: " . $infon->nivel . " falta: " . $falta;
                }else{
                    return "burrr";
                }*/


            }

            $arrayInsigOrdenado = $arrayInsignias->sortBy('titulo');
            $arrayInsigOrdenadoArray = $arrayInsigOrdenado->toArray();

            $solo5Insignias = array_slice($arrayInsigOrdenadoArray, 0, 5);




            return ['success' => 1,
                'devocional' => $arrayLecturaDia];


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
}
