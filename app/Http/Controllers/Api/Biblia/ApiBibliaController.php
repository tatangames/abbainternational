<?php

namespace App\Http\Controllers\Api\Biblia;

use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBlockTexto;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulos;
use App\Models\BibliaCapitulosTextos;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use App\Models\Versiculo;
use App\Models\VersiculoRefran;
use App\Models\VersiculoTextos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiBibliaController extends Controller
{

    public function listadoBiblias(Request $request){

        $rules = array(
            'iduser' => 'required',
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $listado = Biblias::where('visible', 1)
                ->orderBy('posicion', 'ASC')->get();

            $hayinfo = 0;
            foreach ($listado as $dato){
                $hayinfo = 1;

                $titulo = $this->retornoTituloBiblia($dato->id);
                $dato->titulo = $titulo;
            }

            return ['success' => 1,
                'hayinfo' => $hayinfo,
                'listado' => $listado];
        }
        else{
            return ['success' => 99];
        }
    }


    // defecto español
    private function retornoTituloBiblia($idbiblia){

        $datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }



    public function listadoBibliasCapitulos(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idbiblia' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // por el momento no se utilizara
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {

            // listado de capitulos
            $arrayCapitulos = BibliaCapitulos::where('id_biblias', $request->idbiblia)
                ->where('visible', 1)
                ->orderBy('posicion', 'ASC')
                ->get();

            $resultsBloque = array();
            $index = 0;

            foreach ($arrayCapitulos as $dato){
                array_push($resultsBloque, $dato);

                $raw = $this->retornoTituloCapitulo($dato->id);
                $dato->titulo = $raw;


                $arrayBloques = BibliaCapituloBloque::where('id_biblia_capitulo', $dato->id)
                    ->where('visible', 1)
                    ->orderBy('posicion', 'ASC')
                    ->get();

                foreach ($arrayBloques as $fila){
                    $titulo = $this->retornoTituloCapituloBoque($fila->id);
                    $fila->titulo = $titulo;
                }

                $resultsBloque[$index]->detalle = $arrayBloques;
                $index++;
            }

            return ['success' => 1,
                'listado' => $arrayCapitulos,
            ];
        }else{
            return ['success' => 99];
        }
    }




    private function retornoTituloCapitulo($idcapitulo)
    {
        $datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idcapitulo)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }


    // TITULO CAPITULO BLOQUE
    private function retornoTituloCapituloBoque($idcapiblock){

        $datos = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idcapiblock)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }



    public function listadoCapitulosVersiculos(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idcapibloque' => 'required'
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // por el momento no se utilizara
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {

            // listado de versiculos
            $arrayVersiculos = Versiculo::where('id_capitulo_block', $request->idcapibloque)
                ->where('visible', 1)
                ->orderBy('posicion', 'ASC')
                ->get();

            foreach ($arrayVersiculos as $dato){

                $titulo = $this->retornoTituloVersiculo($dato->id);
                $dato->titulo = $titulo;
            }

            return ['success' => 1,
                'listado' => $arrayVersiculos,
            ];
        }else{
            return ['success' => 99];
        }
    }



    // TITULO DE VERSICULO
    private function retornoTituloVersiculo($idversiculo){

        $datos = VersiculoTextos::where('id_versiculo', $idversiculo)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }



    public function listadoTextosVersiculos(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idversiculo' => 'required'
        );


        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // por el momento no se utilizara
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {

            $infoVersiculo = Versiculo::where('id', $request->idversiculo)->first();
            $infoCapiBlock = BibliaCapituloBloque::where('id', $infoVersiculo->id_capitulo_block)->first();
            $infoCapitulo = BibliaCapitulos::where('id', $infoCapiBlock->id_biblia_capitulo)->first();

            // nombre libro - defecto espanol
            $infoLibroTexto = BibliaCapitulosTextos::where('id_biblia_capitulo', $infoCapitulo->id)->first();
            $nombreLibro = $infoLibroTexto->titulo;

            // numero capitulo - defecto espanol
            $infoCapituloTexto = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $infoCapiBlock->id)->first();
            $numeroCapitulo = $infoCapituloTexto->titulo;

            // texto final concatenado

            $listado = Versiculo::where('id_capitulo_block', $infoCapiBlock)
                ->orderBy('posicion', 'ASC')
                ->get();

            foreach ($listado as $dato){

                $infoVer = VersiculoRefran::where('id_versiculo', $dato->id)->first();
                $dato->titulo = $infoVer->titulo;
            }


            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }





}
