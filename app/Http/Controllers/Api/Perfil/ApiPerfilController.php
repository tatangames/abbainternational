<?php

namespace App\Http\Controllers\Api\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Illuminate\Http\Request;

class ApiPerfilController extends Controller
{

    public function informacionPerfilUsuario(Request $request)
    {

        if($info = Usuarios::where('id', $request->iduser)->first()){

            $fechaNac = date("d-m-Y", strtotime($info->fecha_nacimiento));

            return ['success' => 1,
                'nombre' => $info->nombre,
                'apellido' => $info->apellido,
                'fecha_nacimiento' => $fechaNac,
                'correo' => $info->correo,
                'fecha_nac_raw' => $info->fecha_nacimiento
            ];
        }

        return ['success' => 2];
    }



}
