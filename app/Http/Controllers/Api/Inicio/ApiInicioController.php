<?php

namespace App\Http\Controllers\Api\Inicio;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\Iglesias;
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


            // DEVOLVER

            // 1- refran del dia (sustento diario)
            // 2- Imagenes del Dia
            // Insignias
            // reels video facebook
            // ultimos planes (fecha mas nueva tomar 5)
                // me debera redireccionar a otro fragment y posicionando boton nuevos planes


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




    }
}
