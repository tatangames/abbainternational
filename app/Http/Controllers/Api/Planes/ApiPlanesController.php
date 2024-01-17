<?php

namespace App\Http\Controllers\Api\Planes;

use App\Http\Controllers\Controller;
use App\Models\Planes;
use App\Models\PlanesContenedor;
use App\Models\PlanesContenedorTextos;
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use App\Models\PlanesUsuariosContinuar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiPlanesController extends Controller
{


    public function buscarPlanesNoAgregados(Request $request){

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


            $arrayContenedor = PlanesContenedor::where('visible', 1)->get();

            // todos los planes de mi usuario
            $arrayId = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            // meter todos los planes, menos elegidos por el usuario ya
            foreach ($arrayContenedor as $dato){

                $hayInfo = 0;

                // obtener todos los planes NO elegido por el usuario y sean visible
                $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                    ->where('id_planes_contenedor', $dato->id)
                    ->where('visible', 1)
                    ->get();

                if ($arrayPlanes->isNotEmpty()) {
                    $hayInfo = 1;
                }

                $dato->hayinfo = $hayInfo;
            }


            // EVITAR LOS TITULOS VACIOS
            $arrayPlanesValidos = $this->retornoPlanesNoElegidos($arrayContenedor, $userToken->id, $idiomaTextos);

            return [
                'success' => 1,
                'listado' => $arrayPlanesValidos,
                ];
        }else{
            return ['success' => 99];
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

    // RETORNA POR TITULO Y SUS PLANES DISPONIBLES POR USUARIO
    private function retornoPlanesNoElegidos($arraybuscar, $idusuario, $idiomaTextos){

        // todos los planes de mi usuario
        $arrayId = PlanesUsuarios::where('id_usuario', $idusuario)
            ->select('id_planes')
            ->get();

        $pilaIdContenedor = array();

        foreach ($arraybuscar as $item){
            if($item->hayinfo > 0){
                array_push($pilaIdContenedor, $item->id);
            }
        }

        $planesContenedor = PlanesContenedor::whereIn('id', $pilaIdContenedor)
            ->orderBy('posicion', 'ASC')
            ->get();

        $resultsBloque = array();
        $index = 0;

        foreach ($planesContenedor as $dato){
            array_push($resultsBloque, $dato);


            // obtener el titulo segun idioma
            $tituloContenedor = $this->retornoTituloContenedorPlan($idiomaTextos, $dato->id);
            $dato->titulo = $tituloContenedor;


            // obtener todos los planes NO elegido por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                ->where('id_planes_contenedor', $dato->id)
                ->get();

            foreach ($arrayPlanes as $item){
                $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $item->id);
                $item->titulo = $arrayRaw['titulo'];
                $item->subtitulo = $arrayRaw['subtitulo'];
            }

            $resultsBloque[$index]->detalle = $arrayPlanes;
            $index++;
        }

        return $planesContenedor;
    }


    // RETORNO DE TITULO DEL CONTENEDOR DEL PLAN SEGUN IDIOMA
    private function retornoTituloContenedorPlan($idiomaTextos, $idContenedor){

        // si encuentra idioma solicitado
        if($infoPlanContenedorTexto = PlanesContenedorTextos::where('id_planes_contenedor', $idContenedor)
            ->where('id_idioma_planes', $idiomaTextos)
            ->first()){

            return $infoPlanContenedorTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoPlanContenedorTexto = PlanesContenedorTextos::where('id_planes_contenedor', $idContenedor)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoPlanContenedorTexto->titulo;
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

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

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


    // devuelve lista de planes que no he seleccionado aun, por id contenedor
    public function listadoPlanesContenedor(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idcontenedor' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            // todos los planes de mi usuario
            $arrayId = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            $hayInfo = 0;

            // todos los planes del contenedor, menos seleccionados por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                ->where('id_planes_contenedor', $request->idcontenedor)
                ->get();

            if ($arrayPlanes->isNotEmpty()) {
                $hayInfo = 1;

                foreach ($arrayPlanes as $item){
                    $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $item->id);
                    $item->titulo = $arrayRaw['titulo'];
                    $item->subtitulo = $arrayRaw['subtitulo'];
                }
            }

            return [
                'success' => 1,
                'listaplanes' => $arrayPlanes,
                'hayinfo' => $hayInfo
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

                    $nuevoPlan = new PlanesUsuarios();
                    $nuevoPlan->id_usuario = $userToken->id;
                    $nuevoPlan->id_planes = $request->idplan;
                    $nuevoPlan->fecha = Carbon::now('America/El_Salvador');
                    $nuevoPlan->save();

                    DB::commit();
                    return ['success' => 2];

                }catch(\Throwable $e){
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
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idPlanContinuar = 0;
            $arrayContinuar = null;
            $haycontinuar = 0;
            if($infoContinuar = PlanesUsuariosContinuar::where('id_usuarios', $userToken->id)->first()){
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
                    $dato->barra_progreso = $infoP->barra_progreso;
                }
            }

            // obtener todos los planes menos el de Continuar
           $arrayPlanesUser = PlanesUsuarios::where('id_usuario', $userToken->id)
               ->whereNotIn('id_planes', [$idPlanContinuar])
               ->get();

            foreach ($arrayPlanesUser as $dato){

                $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                $dato->titulo = $titulosRaw['titulo'];
                $dato->subtitulo = $titulosRaw['subtitulo'];

                $infoP = Planes::where('id', $dato->id_planes)->first();
                $dato->imagen = $infoP->imagen;
                $dato->imagenportada = $infoP->imagenportada;
                $dato->barra_progreso = $infoP->barra_progreso;
            }

            $datosOrdenados = $arrayPlanesUser->sortBy('titulo')->values();

            $hayinfo = 0;
            if ($arrayContinuar->isNotEmpty()) {
                $hayinfo = 1;
            }

            if ($datosOrdenados->isNotEmpty()) {
                $hayinfo = 1;
            }


            return ['success' => 1,
                'haycontinuar' => $haycontinuar,
                'listacontinuar' => $arrayContinuar,
                'listaplanes' => $datosOrdenados,
                'hayinfo' => $hayinfo

            ];

        }else{
            return ['success' => 99];
        }

    }




}
