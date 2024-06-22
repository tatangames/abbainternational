<?php

namespace App\Http\Controllers\Api\Correo;

use App\Http\Controllers\Controller;
use App\IdiomaCentral;
use App\Mail\CorreoPasswordMail;
use App\Models\IdiomaSistema;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DateTime;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiCorreoController extends Controller
{

    const IDIOMA_ID_1 = 1; // recuperacion de contrasena

    const IDIOMA_ID_2 = 2; // hola

    const IDIOMA_ID_3 = 3; // se ha solicitado un codigo de recuperacion de contrasena
    const IDIOMA_ID_4 = 4; // su codigo de recuperacion es

    const IDIOMA_ID_5 = 5; // si usted no realizo esta solicitud, puede ignorar este mensaje

    const IDIOMA_ID_6 = 6; // mi caminar con dios


    // solicitud de codigo de recuperacion de contrasena
    public function enviarCorreoRecuperacion(Request $request){

        $rules = array(
            'correo' => 'required',
            'idioma' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        // EL IDIOMA LO MANDA EL TELEFONOO



        if($info = Usuarios::where('correo', $request->correo)->first()){

            // el texto que se enviara al correo segun idioma
            $dataIdioma = $this->idiomaCorreoRecuperacion($request->idioma);

            // codigo aleaotorio
            $codigo = '';
            for($i = 0; $i < 6; $i++) {
                $codigo .= mt_rand(0, 9);
            }

            Usuarios::where('id', $info->id)->update(['codigo_pass' => $codigo]);


            $dataWeb = ["codigo" => $codigo,
                "usuario" => $info->nombre . " " . $info->apellido,
                "recuperar_password" => $dataIdioma[self::IDIOMA_ID_1],
                "hola" => $dataIdioma[self::IDIOMA_ID_2],
                "se_ha_solicitado" => $dataIdioma[self::IDIOMA_ID_3],
                "su_codigo_de" => $dataIdioma[self::IDIOMA_ID_4],
                "si_usted_no_realizo" => $dataIdioma[self::IDIOMA_ID_5],
                "micaminar" => $dataIdioma[self::IDIOMA_ID_6]
            ];


            Mail::to($request->correo)
                ->send(new CorreoPasswordMail($dataWeb, $dataIdioma[self::IDIOMA_ID_1]));

            return ['success' => 2,
                'msj' => 'código enviado'
                ];

        }else{
            return ['success' => 1,
                'msj' => "correo no encontrado"
            ];
        }
    }


    public function verificarCodigoRecuperacion(Request $request){


        $rules = array(
            'codigo' => 'required',
            'correo' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        if($infoUsuario = Usuarios::where('correo', $request->correo)
            ->where('codigo_pass', $request->codigo)
            ->first()){

            // crear un token del usuario encontrado
            $token = JWTAuth::fromUser($infoUsuario);

            return ['success' => 1,
                'msj' => 'verificado',
                'token' => $token
            ];

        }else{
            return ['success' => 2,
                'msj' => "codigo no coincide"
            ];
        }
    }


    // actualizar perfil utilizando token
    public function actualizarNuevaPasswordReseteo(Request $request)
    {
        $rules = array(
            'password' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            Usuarios::where('id', $userToken->id)->update([
                'password' => Hash::make($request->password)
            ]);

            // usuario cambio password
            return ['success' => 1];

        }else{
            // usuario no encontrado
            return ['success' => 2];
        }
    }



    // OBTIENE LOS TEXTOS SEGUN IDIOMA DE LA APP
    private function idiomaCorreoRecuperacion($tipoIdioma){

        // 1: espanol
        // 2: ingles

        $msj1 = "";
        $msj2 = "";
        $msj3 = "";
        $msj4 = "";
        $msj5 = "";
        $msj6 = ""; // mi caminar con dios

        // Buscar en un modelo donde tengo datos
        $datosIdioma = new IdiomaCentral();
        $allTextos = $datosIdioma->retornarTextos($tipoIdioma);

        foreach ($allTextos as $dato){
            if(self::IDIOMA_ID_1 == $dato->id){
                $msj1 = $dato->texto;
            }

            if(self::IDIOMA_ID_2 == $dato->id){
                $msj2 = $dato->texto;
            }

            if(self::IDIOMA_ID_3 == $dato->id){
                $msj3 = $dato->texto;
            }

            if(self::IDIOMA_ID_4 == $dato->id){
                $msj4 = $dato->texto;
            }

            if(self::IDIOMA_ID_5 == $dato->id){
                $msj5 = $dato->texto;
            }

            if(self::IDIOMA_ID_6 == $dato->id){
                $msj6 = $dato->texto;
            }
        }


        return [self::IDIOMA_ID_1 => $msj1,
            self::IDIOMA_ID_2 => $msj2,
            self::IDIOMA_ID_3 => $msj3,
            self::IDIOMA_ID_4 => $msj4,
            self::IDIOMA_ID_5 => $msj5,
            self::IDIOMA_ID_6 => $msj6
        ];
    }

}
