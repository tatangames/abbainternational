<?php

namespace App\Http\Controllers\Api\Planes;

use App\Http\Controllers\Controller;
use App\Models\Planes;
use App\Models\PlanesContenedor;
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    private function retornoPlanesNoElegidos($arraybuscar, $idusuario, $idiomaApp){

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
            ->orderBy('titulo')
            ->get();

        $resultsBloque = array();
        $index = 0;

        foreach ($planesContenedor as $dato){
            array_push($resultsBloque, $dato);

            // obtener todos los planes NO elegido por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                ->where('id_planes_contenedor', $dato->id)
                ->get();

            foreach ($arrayPlanes as $item){
                $arrayRaw = $this->retornoTituloPlan($idiomaApp, $item->id);
                $item->titulo = $arrayRaw['titulo'];
                $item->subtitulo = $arrayRaw['subtitulo'];
            }

            $resultsBloque[$index]->detalle = $arrayPlanes;
            $index++;
        }

        return $planesContenedor;
    }


    // RETORNO DE TITULO DEL PLAN SEGUN IDIOMA
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

            if($infoPlanTextos = PlanesTextos::where('id_planes', $request->idplan)
                ->where('id_idioma_planes', $idiomaTextos)
                ->first()){
                $titulo = $infoPlanTextos->titulo;
                $subtitulo = $infoPlanTextos->subtitulo;
            }

            return ['success' => 1,
                'imagen' => $infoPlan->imagen,
                'titulo' => $titulo,
                'subtitulo' => $subtitulo
                ];
        }else{
            return ['success' => 99];
        }

    }



}
