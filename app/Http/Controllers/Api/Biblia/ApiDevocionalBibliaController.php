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
        if($infoFila = DevocionalCapitulo::where('id_devocional_biblia', $request->iddevobiblia)->first()){


            $infoCapituloBlockTexto = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $infoFila->id_capitulo_bloque)
                ->where('id_idioma_planes', 1) // defecto espanol
                ->first();



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



                $contenidoHtml .= "$infoCapituloBlockTexto->textocapitulo";


            $contenidoHtml .= "</body>
                    </html>";



            return ['success' => 1,
                'contenido' => $contenidoHtml,
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
