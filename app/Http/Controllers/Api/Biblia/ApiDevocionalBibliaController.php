<?php

namespace App\Http\Controllers\Api\Biblia;

use App\FuentesCssLetra;
use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBlockTexto;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulosTextos;
use App\Models\BibliasTextos;
use App\Models\DevocionalBiblia;
use App\Models\DevocionalCapitulo;
use App\Models\Versiculo;
use App\Models\VersiculoRefran;
use App\Models\VersiculoTextos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiDevocionalBibliaController extends Controller
{

    public function listadoCapituloVersiculo(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
            'idiomaplan' => 'required',
            'iddevobiblia' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        // ya viene el ID cual sera el POR DEFECTO
        if($infoFila = DevocionalBiblia::where('id', $request->iddevobiblia)->first()){

            $listado = DevocionalCapitulo::where('id_devocional_biblia', $request->iddevobiblia)->get();


            // saber si hay mas opciones de biblias
            $arrayMas = DevocionalBiblia::where('id_bloque_detalle', $infoFila->id_bloque_detalle)
                ->where('id', '!=', $infoFila->id)->get();

            $haymasversiones = 0;

            foreach ($arrayMas as $dato){
                if(DevocionalCapitulo::where('id_devocional_biblia', $dato->id)->first()){
                    $haymasversiones = 1;
                    break;
                }
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



                        </script>
                    </head>
                    <body>";



            foreach ($listado as $dato){

                $infoBloque = BibliaCapituloBloque::where('id', $dato->id_capitulo_bloque)->first();
                $dato->posicioncapi = $infoBloque->posicion;

                // nombre del libro
                $tituloLibro = $this->retornoTituloLibro($infoBloque->id_biblia_capitulo);
                $dato->titulolibro = $tituloLibro;

                // nombre del capitulo
                $tituloCapitulo = $this->retornoTituloCapitulo($dato->id_capitulo_bloque);
                $dato->titulocapitulo = $tituloCapitulo;


                $arrayVersiculo = Versiculo::where('id_capitulo_block', $dato->id_capitulo_bloque)
                    ->where('visible', 1)
                    ->orderBy('posicion', 'ASC')
                    ->get();


                $contenidoHtml .= "<p style='text-align: center; font-size: 22px; margin-bottom: 5px;'>" . $tituloLibro . "</p>"
                . "<p style='text-align: center; font-size: 48px; font-weight: bold; margin-top: 5px;'><strong>" . $tituloCapitulo . "</strong></p>" ;


                foreach ($arrayVersiculo as $fila){

                    $tituloVersiculo = $this->retornoTituloVersiculoTexto($fila->id);

                    $textoSinP = preg_replace('/<p[^>]*>|<\/p>/', '', $tituloVersiculo);

                    $contenidoHtml .= $textoSinP;
                }


                $contenidoHtml .= "<br><br>";
            }


            $contenidoHtml .= "</body>
                    </html>";



            // BUSCAR LAS VERSIONES QUE REALMENTE TIENEN DATOS

            $pilaIdVersiones= array();



            // obtener todas la versiones disponibles
            $arrayVerificar = DevocionalBiblia::where('id_bloque_detalle', $infoFila->id_bloque_detalle)->get();

            foreach ($arrayVerificar as $dato){

                // puede haber varios id repetidos, pero se arregla al hacer la consulta final
                if(DevocionalCapitulo::where('id_devocional_biblia', $dato->id)->first()){
                    array_push($pilaIdVersiones, $dato->id);
                }
            }

            $arrayVersiones = DevocionalBiblia::whereIn('id', $pilaIdVersiones)->get();

            foreach ($arrayVersiones as $dato){
                $titulo = $this->retornoTituloBiblia($dato->id_biblia);
                $dato->titulo = $titulo;
            }

            return ['success' => 1,
                'haymasversiones' => $haymasversiones,
                'contenido' => $contenidoHtml,
                'versiones' => $arrayVersiones
                ];

        }else{
            return ['success' => 99];
        }
    }

    private function retornoTituloBiblia($idbiblia){

        if($datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first()){
            return $datos->titulo;
        }else{
            return "";
        }
    }


    private function retornoTituloVersiculoTexto($idversiculo){

        if($datos = VersiculoRefran::where('id_versiculo', $idversiculo)
            ->where('id_idioma_planes', 1)
            ->first()){

            return $datos->titulo;
        }else{
            return "";
        }
    }


    private function retornoTituloLibro($idbiblialibro){

        if($datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idbiblialibro)
            ->where('id_idioma_planes', 1)
            ->first()){

            return $datos->titulo;
        }else{
            return "";
        }
    }


    private function retornoTituloCapitulo($idbibliacapi){

        if($datos = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idbibliacapi)
            ->where('id_idioma_planes', 1)
            ->first()){

            return $datos->titulo;
        }else{
            return "";
        }
    }


}
