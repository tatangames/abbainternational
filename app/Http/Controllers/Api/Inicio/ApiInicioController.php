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
            'iduser' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            // DEVOLVER

            // 1- refran del dia (sustento diario)
            // 2- Imagenes del Dia
            // Insignias
            // reels video facebook
            // ultimos planes (fecha mas nueva tomar 5)
                // me debera redireccionar a otro fragment y posicionando boton nuevos planes





        }else{
            return ['success' => 99];
        }
    }
}
