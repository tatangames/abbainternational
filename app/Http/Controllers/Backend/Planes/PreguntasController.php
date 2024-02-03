<?php

namespace App\Http\Controllers\Backend\Planes;

use App\Http\Controllers\Controller;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\IdiomaPlanes;
use App\Models\ImagenPreguntas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PreguntasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de pais
    public function indexPreguntas($idplanbloquedetalle){


        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.vistapreguntas', compact('idplanbloquedetalle'));
    }


    // regresa tabla listado de paises
    public function tablaPreguntas($idplanbloquedetalle){


        $listado = BloquePreguntas::where('id_plan_block_detalle', $idplanbloquedetalle)
        ->orderBy('posicion', 'ASC')
            ->get();

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.tablapreguntas', compact('listado'));
    }


    // vista a nuevas preguntas
    public function indexNuevasPreguntas($idplanbloquedetalle)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayImagenes = ImagenPreguntas::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.nuevo.vistanuevapregunta', compact('idplanbloquedetalle',
        'arrayIdiomas', 'arrayImagenes'));
    }


    // registrar nueva pregunta
    public function registrarNuevaPregunta(Request $request)
    {

        $regla = array(
            'idplanbloquedetalle' => 'required',
            'toggle' => 'required',
            'idimagen' => 'required',
        );


        // array: infoIdIdioma, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            Log::info($request->all());

            if($info = BloquePreguntas::where('id_plan_block_detalle', $request->idplanbloquedetalle)
                ->orderBy('posicion', 'DESC')
                ->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }


            $nuevo = new BloquePreguntas();
            $nuevo->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo->id_imagen_pregunta = $request->idimagen;
            $nuevo->visible = 1; // por defecto dejar en visible
            $nuevo->posicion = $nuevaPosicion;
            $nuevo->requerido = $request->toggle;
            $nuevo->save();

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                $detalle = new BloquePreguntasTextos();
                $detalle->id_bloque_preguntas = $nuevo->id;
                $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                $detalle->texto = $filaArray['infoDescripcion'];
                $detalle->save();
            }

            // completado y actualizado
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function actualizarPosicionPreguntas(Request $request)
    {
        $tasks = BloquePreguntas::all();

        foreach ($tasks as $task) {
            $id = $task->id;

            foreach ($request->order as $order) {
                if ($order['id'] == $id) {
                    $task->update(['posicion' => $order['posicion']]);
                }
            }
        }
        return ['success' => 1];
    }


    public function indexEditarBloquePregunta($idbloquepregunta)
    {

        $infoPregunta = BloquePreguntas::where('id', $idbloquepregunta)->first();

        $arrayImagenes = ImagenPreguntas::orderBy('nombre', 'ASC')->get();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $arrayBloquePreguntasTextos = BloquePreguntasTextos::where('id_bloque_preguntas', $idbloquepregunta)
            ->orderBy('id_idioma_planes', 'ASC')
            ->get();


        $contador = 0;
        foreach ($arrayBloquePreguntasTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }


        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.editar.vistaeditarpregunta', compact('infoPregunta',
        'arrayBloquePreguntasTextos', 'arrayImagenes', 'arrayIdiomas'));
    }


    public function editarBloquePreguntas(Request $request)
    {
        $regla = array(
            'idbloquepreguntas' => 'required',
            'idimagen' => 'required',
            'toggle' => 'required'
        );


        // array: infoIdBloquePreguntaTextos, infoIdIdioma, infoDescripcion


        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {


            $datosContenedor = json_decode($request->contenedorArray, true);


            // actualizar
            BloquePreguntas::where('id', $request->idbloquepreguntas)->update([
                'id_imagen_pregunta' => $request->idimagen,
                'requerido' => $request->toggle,
            ]);


            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoPreguntaTexto = BloquePreguntasTextos::where('id', $filaArray['infoIdBloquePreguntaTextos'])->first()){

                    // actualizar
                    BloquePreguntasTextos::where('id', $infoPreguntaTexto->id)->update([
                        'texto' => $filaArray['infoDescripcion'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new BloquePreguntasTextos();
                    $detalle->id_bloque_preguntas = $request->idbloquepreguntas;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->texto = $filaArray['infoDescripcion'];
                    $detalle->save();
                }
            }

            // completado y actualizado
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }





}
