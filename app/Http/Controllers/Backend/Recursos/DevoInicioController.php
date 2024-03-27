<?php

namespace App\Http\Controllers\Backend\Recursos;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionarioTextos;
use App\Models\LecturaDia;
use App\Models\Planes;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBloques;
use App\Models\PlanesBloquesTextos;
use App\Models\PlanesTextos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DevoInicioController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de devocionales inicio
    public function indexDevoInicio(){
        return view('backend.admin.recursos.devocionalinicio.vistadevoinicio');
    }

    // regresa tabla de devocionales inicio
    public function tablaDevoInicio(){

        $listado = LecturaDia::orderBy('id', 'ASC')->get();

         foreach ($listado as $dato){

             $infoBlockDetalle = PlanesBlockDetalle::where('id', $dato->id_planes_block_detalle)->first();
             $infoPlanBloque = PlanesBloques::where('id', $infoBlockDetalle->id_planes_bloques)->first();
             $fechaFormat = date("d-m-Y", strtotime($infoPlanBloque->fecha_inicio));

             $dato->fechaFormat = $fechaFormat;

             $infoPlanTexto = PlanesTextos::where('id_planes', $infoPlanBloque->id_planes)
                 ->where('id_idioma_planes', 1)
                 ->first();

             $dato->titulo = $infoPlanTexto->titulo;
         }

        return view('backend.admin.recursos.devocionalinicio.tabladevoinicio', compact('listado'));
    }

    public function borrarDevoInicio(Request $request){

        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if(LecturaDia::where('id', $request->id)->first()){

            LecturaDia::where('id', $request->id)->delete();

            // borrada
            return ['success' => 1];
        }else{
            // decir que fue borrada
            return ['success' => 1];
        }
    }



    public function indexDevoInicioPlanes(){
        return view('backend.admin.recursos.devocionalinicio.listaplanes.vistadevoplanesinicio');
    }

    public function tablaDevoInicioPlanes(){

        $listado = Planes::orderBy('posicion', 'ASC')
            ->where('visible', 1)
            ->get();

        foreach ($listado as $dato){

            // siempre habra ididoma espanol
            $infoTituloEspanol = PlanesTextos::where('id_planes', $dato->id)
                ->where('id_idioma_planes', 1)
                ->first();

            $dato->titulo = $infoTituloEspanol->titulo;

            $fechaFormat = date("Y-m-d", strtotime($dato->fecha));

            $dato->fecha = $fechaFormat;
        }

        return view('backend.admin.recursos.devocionalinicio.listaplanes.tabladevoplanesinicio', compact('listado'));
    }


    public function indexPlanesBloques($idplan){
        return view('backend.admin.recursos.devocionalinicio.listaplanes.bloques.vistabloquesplan', compact('idplan'));

    }

    public function tablaPlanesBloques($idplan){

        $listado = PlanesBloques::where('id_planes', $idplan)
            ->where('visible', 1)
            ->get();

        foreach ($listado as $dato){
            $fechaFormat = date("d-m-Y", strtotime($dato->fecha_inicio));
            $dato->fechaFormat = $fechaFormat;
        }

        return view('backend.admin.recursos.devocionalinicio.listaplanes.bloques.tablabloquesplan', compact('listado'));
    }



    public function indexPlanesBloquesDetalle($idplanbloque){


        return view('backend.admin.recursos.devocionalinicio.listaplanes.bloquesdetalle.vistabloquesdetalle', compact('idplanbloque'));
    }

    public function tablaPlanesBloquesDetalle($idplanbloque){

        $listado = PlanesBlockDetalle::where('id_planes_bloques', $idplanbloque)
            ->where('visible', 1)
            ->get();

        foreach ($listado as $dato){
            $titulo = $this->retornoTituloBloquesTextos($dato->id);
            $dato->titulo = $titulo;

            $hayTextoDevocional = 0;
            // NO IMPORTA EL IDIOMA, YA QUE SIEMPRE SE AGREGARA ESPAÑOL AL CREARSE
            if(BloqueCuestionarioTextos::where('id_bloque_detalle', $dato->id)->first()){
                $hayTextoDevocional = 1;
            }

            $dato->haydevocional = $hayTextoDevocional;
        }


        return view('backend.admin.recursos.devocionalinicio.listaplanes.bloquesdetalle.tablabloquesdetalle', compact('listado'));
    }

    // RETORNA TITULO DEL BLOQUE DETALLE TEXTOS
    private function retornoTituloBloquesTextos($idBlockDetalle){

        // solo buscar donde esta español

        $infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDetalle)
            ->where('id_idioma_planes', 1)
            ->first();

        return $infoTituloTexto->titulo;
    }


    public function infoDevocionalTexto(Request $request){

        $rules = array(
            'idplanesblockdetalle' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($info = BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idplanesblockdetalle)
            ->where('id_idioma_planes', 1)
            ->first()){

            return ['success' => 1,
                    'texto' => $info->titulo_dia];
        }

        return ['success' => 2];
    }


    public function seleccionarLecturaDia(Request $request){

        $rules = array(
            'idplanesblockdetalle' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        $miBlockDetalle = PlanesBlockDetalle::where('id', $request->idplanesblockdetalle)->first();
        $miPlanBloque = PlanesBloques::where('id', $miBlockDetalle->id_planes_bloques)->first();
        $fechaViene = Carbon::parse($miPlanBloque->fecha_inicio);

        Log::info("fecha viene " . $fechaViene);
        if(LecturaDia::where('id_planes_block_detalle', $request->idplanesblockdetalle)->first()){


            // NO AGREGAR PORQUE YA EXISTE EL MISMO
            $fechaFormat = date("d-m-Y", strtotime($miPlanBloque->fecha_inicio));
            return ['success' => 1, 'fecha' => $fechaFormat];
        }else{

            // buscar que no este de la misma fecha
            $listado = LecturaDia::all(); // id_planes_block_detalle
            foreach ($listado as $dato){

                $infoBlockDetalle = PlanesBlockDetalle::where('id', $dato->id_planes_block_detalle)->first();
                $infoPlanBloque = PlanesBloques::where('id', $infoBlockDetalle->id_planes_bloques)->first();


                // esta es la fecha que ya esta registrada
                $fechaPlanesBloques = Carbon::parse($infoPlanBloque->fecha_inicio);

                // se debe comparar con la fecha que viene
                if($fechaViene->isSameDay($fechaPlanesBloques)){
                    // se manda la fecha que quiere registrar

                    $fechaFormat = date("d-m-Y", strtotime($infoPlanBloque->fecha_inicio));
                    return ['success' => 1, 'fecha' => $fechaFormat];
                }
            }

            // si pasa validaciones se puede registar

            $nuevo = new LecturaDia();
            $nuevo->id_planes_block_detalle = $request->idplanesblockdetalle;
            $nuevo->save();

            return ['success' => 2];
        }
    }


}
