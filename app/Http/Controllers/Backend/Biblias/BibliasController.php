<?php

namespace App\Http\Controllers\Backend\Biblias;

use App\Http\Controllers\Controller;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use App\Models\IdiomaPlanes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use function Symfony\Component\Translation\t;

class BibliasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function vistaBiblias(){
        return view('backend.admin.biblias.vistabiblia');
    }

    public function tablaBiblias(){

        $listado = Biblias::orderBy('posicion', 'ASC')->get();

        foreach ($listado as $dato){
            $titulo = $this->retornoTituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.biblias.tablabiblia', compact('listado'));
    }




    // Por defecto solo sera idioma español
    public function registrarBiblia(Request $request){

        $rules = array(
            'titulo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            if($info = Biblias::orderBy('posicion', 'DESC')->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }


            $nuevo = new Biblias();
            $nuevo->visible = 0;
            $nuevo->posicion = $nuevaPosicion;
            $nuevo->save();

            $detalle = new BibliasTextos();
            $detalle->id_biblias = $nuevo->id;
            $detalle->id_idioma_planes = 1;
            $detalle->titulo = $request->titulo;
            $detalle->save();

            // completado y actualizado
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



    public function informacionBiblia(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = Biblias::where('id', $request->id)->first()){

            $titulo = $this->retornoTituloBiblia($lista->id);

            return ['success' => 1, 'titulo' => $titulo];
        }else{
            return ['success' => 2];
        }
    }




    private function retornoTituloBiblia($idbiblia){

        $datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }

    public function actualizarPosicionBiblias(Request $request){

        Log::info($request->all());


        $tasks = Biblias::all();

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

    public function actualizarBiblia(Request $request){

        $rules = array(
            'idbiblia' => 'required',
            'titulo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        // actualizar texto espanol
        BibliasTextos::where('id_biblias', $request->idbiblia)
            ->where('id_idioma_planes', 1)
            ->update([
                'titulo' => $request->titulo,
            ]);

        return ['success' => 1];
    }

    public function estadoBiblia(Request $request)
    {
        $regla = array(
            'idbiblia' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        Biblias::where('id', $request->idbiblia)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }


}
