<?php

namespace App\Http\Controllers\Backend\Devocional;

use App\Http\Controllers\Controller;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use Illuminate\Http\Request;

class DevoBibliaController extends Controller
{

    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function vistaDevoBiblia($idbloqedetalle)
    {

        $arrayBiblias = Biblias::orderBy('posicion', 'ASC')->get();

        foreach ($arrayBiblias as $dato){
            $titulo = $this->retornoTituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.devocionalbiblia.vistadevobiblia', compact('idbloqedetalle', 'arrayBiblias'));
    }

    public function tablaDevoBiblia($idbloqedetalle)
    {

        return "tabla";

        return view('backend.admin.devocionalbiblia.tabladevobiblia');
    }


    private function retornoTituloBiblia($idbiblia){

        $datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }

}
