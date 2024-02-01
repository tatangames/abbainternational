<?php

namespace App;

use App\Models\IdiomaSistema;

class IdiomaCentral
{

    // UTLIZADO PARA RECUPERAR UNOS TEXTOS DEL IDIOMA SISTEMA, EJEMPLO PARA
    // TEXTOS DE CORREO PLANTILLA
    public function retornarTextos($tipoIdioma){

        if($tipoIdioma == 1){// espanol
            $arrayTextos = IdiomaSistema::select('id', 'espanol AS texto')->get();
        }
        else if($tipoIdioma == 2){ // ingles
            $arrayTextos = IdiomaSistema::select('id', 'ingles AS texto')->get();
        }
        else{
            // defecto espanol
            $arrayTextos = IdiomaSistema::select('id', 'espanol AS texto')->get();
        }

        return $arrayTextos;
    }
}
