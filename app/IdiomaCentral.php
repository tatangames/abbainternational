<?php

namespace App;

use App\Models\IdiomaSistema;

class IdiomaCentral
{

    public function retornarTextos($tipoIdioma){

        if($tipoIdioma == 0){// espanol
            $arrayTextos = IdiomaSistema::select('id', 'espanol AS texto')->get();
        }
        else if($tipoIdioma == 1){ // ingles
            $arrayTextos = IdiomaSistema::select('id', 'ingles AS texto')->get();
        }


        else{
            $arrayTextos = IdiomaSistema::select('id', 'espanol AS texto')->get();
        }

        return $arrayTextos;
    }
}
