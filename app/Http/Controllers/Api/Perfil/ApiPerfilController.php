<?php

namespace App\Http\Controllers\Api\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuarios;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;


class ApiPerfilController extends Controller
{


    // informacion de mi perfil
    public function informacionPerfilUsuario(Request $request)
    {
        $rules = array(
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $fechaNac = date("d-m-Y", strtotime($userToken->fecha_nacimiento));

            $hayImagen = 0;
            if($userToken->imagen != null){
                $hayImagen = 1;
            }

            return ['success' => 1,
                'nombre' => $userToken->nombre,
                'apellido' => $userToken->apellido,
                'fecha_nacimiento' => $fechaNac,
                'correo' => $userToken->correo,
                'fecha_nac_raw' => $userToken->fecha_nacimiento,
                'hayimagen' => $hayImagen,
                'imagen' => $userToken->imagen
            ];
        }else{
            return ['success' => 2];
        }
    }

    // cuabre se abre fragment ajustes
    public function informacionAjustes(Request $request){

        $rules = array(
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $info = Usuarios::where('id', $userToken->id)->first();

            $primeraLetra = substr($info->nombre, 0, 1);

            return ['success' => 1,
                'letra' => $primeraLetra,
                'nombre' => $info->nombre . " " . $info->apellido
            ];

        }else{
            return ['success' => 2];
        }
    }


    // actualizar datos de mi perfil
    public function actualizarPerfilUsuario(Request $request){

        $rules = array(
            'iduser' => 'required',
            'nombre' => 'required',
            'apellido' => 'required',
            'fechanac' => 'required',
            'correo' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        // sacar usuario del token
        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if(Usuarios::where('correo', $request->correo)
                ->where('id', '!=', $userToken->id)->first()){
                return ['success' => 1, 'mensaje' => 'Correo ya registrado'];
            }

            // actualizar

            if ($request->hasFile('imagen')) {

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->imagen->getClientOriginalExtension();
                $nombreFoto = $nombre . strtolower($extension);
                $avatar = $request->file('imagen');
                $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

                if ($upload) {

                    $imagenOld = $userToken->imagen;

                    Usuarios::where('id', $userToken->id)
                        ->update([
                            'nombre' => $request->nombre,
                            'apellido' => $request->apellido,
                            'fecha_nacimiento' => $request->fechanac,
                            'correo' => $request->correo,
                            'imagen' => $nombreFoto
                        ]);

                    if($imagenOld != null){
                        if(Storage::disk('archivos')->exists($imagenOld)){
                            Storage::disk('archivos')->delete($imagenOld);
                        }
                    }

                    return ['success' => 2];


                } else {
                    Log::info("entra 1");
                    // error al subir imagen
                    return ['success' => 99];
                }


            }else{
               // no llego imagen

                Usuarios::where('id', $userToken->id)
                    ->update([
                        'nombre' => $request->nombre,
                        'apellido' => $request->apellido,
                        'fecha_nacimiento' => $request->fechanac,
                        'correo' => $request->correo,
                    ]);

                return ['success' => 2];
            }

            return ['success' => 2];
        }else{
            return ['success' => 99];
        }
    }


    // actualizar perfil utilizando token
    public function actualizarPassword(Request $request)
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







}
