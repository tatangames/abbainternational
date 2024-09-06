<?php

namespace App\Http\Controllers\Api\Biblia;

use App\FuentesCssLetra;
use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBlockTexto;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulos;
use App\Models\BibliaCapitulosTextos;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use App\Models\VersiculoRefran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiBibliaController extends Controller
{

    public function listadoBiblias(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idioma' => 'required'
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
                ->orderBy('posicion', 'ASC')
                ->get();

            foreach ($listado as $dato){

                $titulo = $this->retornoTituloBiblia($dato->id, $request->idioma);
                $dato->titulo = $titulo;
            }

            return ['success' => 1, 'listado' => $listado];
        }
        else{
            return ['success' => 99];
        }
    }


    private function retornoTituloBiblia($idbiblia, $ididioma){

        $titulo = "";
        if($datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', $ididioma)
            ->first()){
            $titulo = $datos->titulo;
        }

        return $titulo;
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

                $raw = $this->retornoTituloCapitulo($dato->id, $idiomaTextos);
                $dato->titulo = $raw;


                $arrayBloques = BibliaCapituloBloque::where('id_biblia_capitulo', $dato->id)
                    ->where('visible', 1)
                    ->orderBy('posicion', 'ASC')
                    ->get();

                foreach ($arrayBloques as $fila){
                    $titulo = $this->retornoTituloCapituloBoque($fila->id, $idiomaTextos);
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



    private function retornoTituloCapitulo($idcapitulo, $idioma)
    {
        $titulo = "";
        if($datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idcapitulo)
            ->where('id_idioma_planes', $idioma)
            ->first()){
            $titulo = $datos->titulo;
        }

        return $titulo;
    }


    // TITULO CAPITULO BLOQUE
    private function retornoTituloCapituloBoque($idcapiblock, $idioma){

        $titulo = "";
        if($datos = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idcapiblock)
            ->where('id_idioma_planes', $idioma)
            ->first()){
            $titulo = $datos->titulo;
        }

        return $titulo;
    }





    public function listadoTextosCapituloBiblia(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idcapitulo' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // POR EL MOMENTO NO SE UTILIZA, ES DEFECTO IDIOMA ESPANOL
        $idiomaTextos = $request->idiomaplan;

        if ($userToken = JWTAuth::user($tokenApi)) {

            $texto = "";
            if($info = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $request->idcapitulo)
                ->where('id_idioma_planes', $idiomaTextos)
                ->first()){
                $texto = $info->textocapitulo;
            }

            $contenidoHtml = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $datosFuentes = new FuentesCssLetra();
            $contenidoHtml .= $datosFuentes->retornaFuentesCss();


            $contenidoHtml .= "

                        </style>
                        <script type='text/javascript'>

                            /*function scrollTo(element){
                                document.getElementById(element).scrollIntoView();
                            }*/

                        </script>
                    </head>
                    <body>";

                $contenidoHtml .= "$texto";

            $contenidoHtml .= "</div></body>
                    </html>";

            return ['success' => 1,
                'contenido' => $contenidoHtml];

        }else{
            return ['success' => 99];
        }
    }







}
