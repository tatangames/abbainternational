<?php

namespace App\Http\Controllers\Backend\Devocional;

use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBlockTexto;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulos;
use App\Models\BibliaCapitulosTextos;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use App\Models\DevocionalBiblia;
use App\Models\DevocionalCapitulo;
use App\Models\PlanesBlockDetalle;
use App\Models\Versiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DevoBibliaController extends Controller
{

    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function vistaDevoBiblia($idbloqedetalle)
    {

        $arrayBiblias = Biblias::orderBy('posicion', 'ASC')
            ->where('visible', 1)
            ->get();

        foreach ($arrayBiblias as $dato){
            $titulo = $this->retornoTituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.devocionalbiblia.vistadevobiblia', compact('idbloqedetalle', 'arrayBiblias'));
    }

    public function tablaDevoBiblia($idbloqedetalle)
    {

        // SOLO SE MOSTRARA AL USUARIO EN APP SI TIENE CAPITULOS
        $listado = DevocionalBiblia::where('id_bloque_detalle', $idbloqedetalle)->get();

        foreach ($listado as $dato){

            $titulo = $this->retornoTituloBiblia($dato->id_biblia);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.devocionalbiblia.tabladevobiblia', compact('listado'));
    }


    private function retornoTituloBiblia($idbiblia){

        $datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }


    public function registrarBibliaDevo(Request $request)
    {
        $rules = array(
            'idbloqedetalle' => 'required',
            'idbiblia' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            // SOLO ES PERMITIDO 1 BIBLIA
            if(DevocionalBiblia::where('id_bloque_detalle', $request->idbloqedetalle)->first()){
                return ['success' => 1];
            }

            $nuevo = new DevocionalBiblia();
            $nuevo->id_bloque_detalle = $request->idbloqedetalle;
            $nuevo->id_biblia = $request->idbiblia;
            $nuevo->save();

            DB::commit();
            return ['success' => 2];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



    public function borrarRegistroDevoBiblia(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            // Borrar datos de la segunda tabla
            DevocionalCapitulo::where('id_devocional_biblia', $request->id)->delete();
            DevocionalBiblia::where('id', $request->id)->delete();


            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }





    public function vistaDevoBibliaCapitulos($iddevobiblia)
    {

        $info = DevocionalBiblia::where('id', $iddevobiblia)->first();

        // se debe mandar los Libros
        $arrayLibros = BibliaCapitulos::where('id_biblias', $info->id_biblia)->get();

        foreach ($arrayLibros as $dato){

            $titulo = $this->retornoTituloLibro($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.devocionalbiblia.capitulos.vistadevobibliacapitulo', compact('iddevobiblia', 'arrayLibros'));
    }


    public function tablaDevoBibliaCapitulos($iddevobiblia)
    {

        $listadoCapis = DevocionalCapitulo::where('id_devocional_biblia', $iddevobiblia)->get();

        foreach ($listadoCapis as $dato){

            $infoBloque = BibliaCapituloBloque::where('id', $dato->id_capitulo_bloque)->first();
            $dato->posicion = $infoBloque->posicion;

            // nombre del libro
            $tituloLibro = $this->retornoTituloLibro($infoBloque->id_biblia_capitulo);
            $dato->titulolibro = $tituloLibro;

            // nombre del capitulo
            $tituloCapitulo = $this->retornoTituloCapitulo($dato->id_capitulo_bloque);
            $dato->titulocapitulo = $tituloCapitulo;
        }

        $listado = $listadoCapis->sortBy('posicion')->values();

        return view('backend.admin.devocionalbiblia.capitulos.tabladevobibliacapitulo', compact('listado'));
    }


    private function retornoTituloLibro($idbiblialibro){

        $datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idbiblialibro)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }



    public function buscadorCapitulos(Request $request)
    {
        $rules = array(
            'idlibro' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        $listado = BibliaCapituloBloque::where('id_biblia_capitulo', $request->idlibro)
            ->where('visible', 1)
            ->orderBy('posicion', 'ASC')
            ->get();

        foreach ($listado as $dato){
            $titulo = $this->retornoTituloCapitulo($dato->id);
            $dato->titulo = $titulo;
        }

        return ['success' => 1,
                'listado' => $listado];
    }


    public function buscadorVersiculo(Request $request)
    {
        $rules = array(
            'idcapibloque' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        $listado = Versiculo::where('id_capitulo_block', $request->idcapibloque)
            ->where('visible', 1)
            ->orderBy('posicion', 'ASC')
            ->get();

        foreach ($listado as $dato){
            $titulo = $this->retornoTituloCapitulo($dato->id);
            $dato->titulo = $titulo;
        }

        return ['success' => 1,
            'listado' => $listado];
    }

    private function retornoTituloCapitulo($idbibliacapi){

        $datos = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idbibliacapi)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }


    public function registrarCapitulo(Request $request)
    {


        $rules = array(
            'iddevobiblia' => 'required',
            'idcapitulo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }


        // SOLO SE PUEDE 1 REGISTRO
        if(DevocionalCapitulo::where('id_devocional_biblia', $request->iddevobiblia)->first()){
            return ['success' => 1];
        }

        $nuevo = new DevocionalCapitulo();
        $nuevo->id_devocional_biblia = $request->iddevobiblia;
        $nuevo->id_capitulo_bloque = $request->idcapitulo;
        $nuevo->save();

        return ['success' => 2];
    }


    public function borrarFila(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }


        // evitar meter capitulo duplicado
        if(DevocionalCapitulo::where('id', $request->id)->first()){
            DevocionalCapitulo::where('id', $request->id)->delete();
        }

        return ['success' => 1];
    }



}
