<?php

namespace App\Http\Controllers\Backend\Biblias;

use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulos;
use App\Models\BibliaCapitulosTextos;
use App\Models\BibliasTextos;
use App\Models\BibliaVersiculo;
use App\Models\BibliaVersiculoBloque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BibliaCapituloController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }


    public function vistaCapitulos($idbiblia){

        $nombre = $this->retornoTituloBiblia($idbiblia);

        return view('backend.admin.biblias.capitulos.vistacapitulo', compact('idbiblia', 'nombre'));
    }

    public function tablaCapitulos($idbiblia){

        $listado = BibliaCapitulos::where('id_biblias', $idbiblia)
            ->orderBy('posicion', 'ASC')
            ->get();

        foreach ($listado as $dato){

            $titulo = $this->retornoTituloCapituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.biblias.capitulos.tablacapitulo', compact('listado'));
    }

    private function retornoTituloCapituloBiblia($idcapitulo){

        $datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idcapitulo)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }

    private function retornoTituloBiblia($idbiblia){

        $datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }


    public function registrarCapitulo(Request $request){

        $rules = array(
            'titulo' => 'required',
            'idbiblia' => 'required',

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            if($info = BibliaCapitulos::where('id_biblias', $request->idbiblia)
                ->orderBy('posicion', 'DESC')->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }

            $nuevo = new BibliaCapitulos();
            $nuevo->id_biblias = $request->idbiblia;
            $nuevo->visible = 0;
            $nuevo->posicion = $nuevaPosicion;
            $nuevo->save();

            $detalle = new BibliaCapitulosTextos();
            $detalle->id_biblia_capitulo = $nuevo->id;
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


    public function actualizarPosicionBibliaCapitulos(Request $request){

        Log::info($request->all());

        $tasks = BibliaCapitulos::all();

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


    public function informacionCapitulo(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = BibliaCapitulos::where('id', $request->id)->first()){

            $titulo = $this->retornoTituloCapituloBiblia($lista->id);

            return ['success' => 1, 'titulo' => $titulo];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarCapitulo(Request $request)
    {
        $rules = array(
            'idcapitulo' => 'required',
            'titulo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        // actualizar texto espanol
        BibliaCapitulosTextos::where('id_biblia_capitulo', $request->idcapitulo)
            ->where('id_idioma_planes', 1)
            ->update([
                'titulo' => $request->titulo,
            ]);

        return ['success' => 1];
    }



    public function estadoCapitulo(Request $request)
    {
        $regla = array(
            'idcapitulo' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        BibliaCapitulos::where('id', $request->idcapitulo)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }





    // -------------------------------------------------------------------------

    public function vistaCapitulosBloque($idcapitulo)
    {

        return view('backend.admin.biblias.capitulos.bloque.vistacapitulobloque', compact('idcapitulo'));
    }

    public function tablaCapitulosBloque($idcapitulo)
    {
        $listado = BibliaCapituloBloque::where('id_biblia_capitulo', $idcapitulo)
            ->orderBy('numero', 'ASC')
            ->get();

        return view('backend.admin.biblias.capitulos.bloque.tablacapitulobloque', compact('listado'));
    }


    public function registrarCapituloBloque(Request $request)
    {
        $rules = array(
            'idcapitulo' => 'required',
            'numero' => 'required',

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new BibliaCapituloBloque();
            $nuevo->id_biblia_capitulo = $request->idcapitulo;
            $nuevo->visible = 0;
            $nuevo->numero = $request->numero;
            $nuevo->save();

            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function informacionCapituloBloque(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = BibliaCapituloBloque::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarCapituloBloque(Request $request)
    {
        $rules = array(
            'idbloque' => 'required',
            'numero' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        BibliaCapituloBloque::where('id', $request->idbloque)
            ->update([
                'numero' => $request->numero,
            ]);

        return ['success' => 1];
    }


    public function estadoCapituloBloque(Request $request)
    {
        $regla = array(
            'idbloque' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        BibliaCapituloBloque::where('id', $request->idbloque)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }






// -------------------------------------------------------------------------

    public function vistaCapitulosBloqueVersiculo($idbloque)
    {
        return view('backend.admin.biblias.capitulos.versiculos.vistaversiculo', compact('idbloque'));
    }

    public function tablaCapitulosBloqueVersiculo($idbloque)
    {
        $listado = BibliaVersiculoBloque::where('id_biblia_capitulo_bloque', $idbloque)
            ->orderBy('numero', 'ASC')
            ->get();

        return view('backend.admin.biblias.capitulos.versiculos.tablaversiculo', compact('listado'));
    }


    public function registrarCapituloBloqueVersiculo(Request $request)
    {
        $rules = array(
            'idbloque' => 'required',
            'numero' => 'required',

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new BibliaVersiculoBloque();
            $nuevo->id_biblia_capitulo_bloque = $request->idbloque;
            $nuevo->visible = 0;
            $nuevo->numero = $request->numero;
            $nuevo->save();

            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function informacionCapituloBloqueVersiculo(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = BibliaVersiculoBloque::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarCapituloBloqueVersiculo(Request $request)
    {
        $rules = array(
            'idbloque' => 'required',
            'numero' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        BibliaVersiculoBloque::where('id', $request->idbloque)
            ->update([
                'numero' => $request->numero,
            ]);

        return ['success' => 1];
    }


    public function estadoCapituloBloqueVersiculo(Request $request)
    {
        $regla = array(
            'idbloque' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        BibliaVersiculoBloque::where('id', $request->idbloque)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }



}
