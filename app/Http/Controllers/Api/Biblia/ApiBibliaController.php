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


            $listado = Versiculo::where('id_capitulo_block', $infoCapiBlock->id)
                ->orderBy('posicion', 'ASC')
                ->get();


            $contenidoHtml = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $contenidoHtml .= $this->retornoFuentesCSS();




            $contenidoHtml .= "

                        </style>
                        <script type='text/javascript'>

                            function disminuirTamano() {
                                 var elementos = document.querySelectorAll('*'); // Obtener todos los elementos
                                    elementos.forEach(function(elemento) {
                                        // Verificar si el elemento es una etiqueta de texto
                                        if (elemento.nodeType === Node.TEXT_NODE && elemento.parentNode.nodeName !== 'SCRIPT') {
                                            var estilo = window.getComputedStyle(elemento.parentNode, null); // Obtener el estilo calculado del elemento padre
                                            var tamanoActual = parseInt(estilo.getPropertyValue('font-size')); // Obtener el tamaño de la fuente actual del elemento padre
                                            var nuevoTamano = tamanoActual + 2; // Aumentar el tamaño en 2px
                                            elemento.parentNode.style.fontSize = nuevoTamano + 'px'; // Aplicar el nuevo tamaño de fuente al elemento padre
                                        }
                                    });
                            }

                            function aumentarTamano() {
                                   var elementos = document.querySelectorAll('*'); // Obtener todos los elementos
                                    elementos.forEach(function(elemento) {
                                        // Verificar si el elemento es una etiqueta de texto
                                            var estilo = window.getComputedStyle(elemento.parentNode, null); // Obtener el estilo calculado del elemento padre
                                            var tamanoActual = parseInt(estilo.getPropertyValue('font-size')); // Obtener el tamaño de la fuente actual del elemento padre
                                            var nuevoTamano = tamanoActual - 2; // Aumentar el tamaño en 2px
                                            elemento.parentNode.style.fontSize = nuevoTamano + 'px'; // Aplicar el nuevo tamaño de fuente al elemento padre

                                    });
                            }


                            function scrollTo(element){
                                document.getElementById(element).scrollIntoView();
                            }


                        </script>
                    </head>
                    <body>"


                        . "<p style='text-align: center; font-size: 22px; margin-bottom: 5px;'>" . $nombreLibro . "</p>"
                        . "<p style='text-align: center; font-size: 35px; font-weight: bold; margin-top: 5px;'>" . $numeroCapitulo . "</p>" ;

                  foreach ($listado as $dato) {

                      $infoVer = VersiculoRefran::where('id_versiculo', $dato->id)->first();
                      $dato->titulo = $infoVer->titulo;

                      $contenidoHtml .= "<div id='verso$dato->id'>$dato->titulo </div>";
                  }

            $contenidoHtml .= "</body>
                    </html>";






            return ['success' => 1,
                'contenido' => $contenidoHtml,

                ];
        }else{
            return ['success' => 99];
        }
    }




    private function retornoFuentesCSS(){

        $fuentes = "

                @font-face {
                    font-family: 'Fuente1';
                    src: url('file:///android_res/font/notosans_light.ttf') format('truetype'); /* Ruta de la tercera fuente */
                 }

                @font-face {
                    font-family: 'Fuente2';
                    src: url('file:///android_res/font/notosans_condensed_medium.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                @font-face {
                    font-family: 'Fuente3';
                    src: url('file:///android_res/font/times_new_normal_regular.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                @font-face {
                    font-family: 'Fuente4';
                    src: url('file:///android_res/font/recolecta_medium.ttf') format('truetype'); /* Ruta de la cuarta fuente */
                }

                @font-face {
                    font-family: 'Fuente5';
                    src: url('file:///android_res/font/recolecta_regular.ttf') format('truetype'); /* Ruta de la quinta fuente */
                }

                /* Utilizar las fuentes según sea necesario */
                .texto-fuente1 {
                    font-family: 'Fuente1', sans-serif;
                }

                .texto-fuente2 {
                    font-family: 'Fuente2', sans-serif;
                }

                .texto-fuente3 {
                    font-family: 'Fuente3', sans-serif;
                }

                .texto-fuente4 {
                    font-family: 'Fuente4', sans-serif;
                }

                .texto-fuente5 {
                    font-family: 'Fuente5', sans-serif;
                }
        ";

        return $fuentes;
    }


}
