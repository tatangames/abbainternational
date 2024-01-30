<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Mail\CorreoTokenPasswordMail;
use App\Models\Administrador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DateTime;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(){
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function index(){
        return view('frontend.login.vistalogin');
    }


    public function indexIngresoDeCorreo(){

        return view('frontend.login.vistaingresarcorreo');
    }

    public function login(Request $request){

        $rules = array(
            'email' => 'required',
            'password' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ( $validator->fails()){
            return ['success' => 0];
        }

        $credentials = request()->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            //return redirect()->route('admin.index');
            return ['success'=> 1, 'ruta'=> route('admin.panel')];
        } else {
            return ['success' => 2];
        }
    }

    public function logout(Request $request){
        Auth::guard('admin')->logout();
        return redirect('/');
    }

    public function enviarCorreoAdministrador(Request $request){

        $rules = array(
            'correo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($info = Administrador::where('email', $request->correo)->first()){

            $token = Str::random(60);

            $fecha = Carbon::now('America/El_Salvador');

            // LA VALIDACION ES PARA 1 HORA


            $url = env("APP_URL", "");

            $urlFinal = $url . "/admin/resetear/contrasena/administrador/" . $token;

            Administrador::where('id', $info->id)
                ->update(['token_correo' => $token,
                    'token_fecha' => $fecha]);

            $data = ["codigo" => $token,
                "usuario" => $info->nombre,
                "url" => $urlFinal];

            Mail::to($info->email)
                ->send(new CorreoTokenPasswordMail($data));

            return ['success' => 1];
        }else{
            // correo no encontrado
            return ['success' => 2];
        }
    }


    public function indexIngresoNuevaPasswordLinkRedireccion(){

        return view('correos.vistalinkexpirado');
    }


    public function indexIngresoNuevaPasswordLink($token){

        // buscar el token

        if($token != null){

            if($info = Administrador::where('token_correo', $token)->first()){

                $fechaRegistro = Carbon::parse($info->token_fecha);

                $tiempoSumado = $fechaRegistro->addMinute(60)->format('Y-m-d H:i:s');
                $fechaHoy = Carbon::now('America/El_Salvador')->format('Y-m-d H:i:s');

                $d1 = new DateTime($tiempoSumado);
                $d2 = new DateTime($fechaHoy);

                if ($d1 > $d2){
                    // PUEDE CAMBIAR CONTRASEÑA

                    return view('frontend.login.vistareseteopassword', compact('token'));

                }else {
                    // YA NO PUEDE CAMBIAR LA CONTRASEÑA, TIEMPO EXPIRADO

                    return view('correos.vistalinkexpirado');
                }

            }else{

                //TOKEN INGRESADO NO ES VALIDO
                return view('correos.vistalinkexpirado');
            }


        }else{
            return view('correos.vistalinkexpirado');
        }
    }


    public function actualizarPasswordAdministrador(Request $request){


        $rules = array(
            'token' => 'required',
            'contrasena' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ( $validator->fails()){
            return ['success' => 0];
        }

        if($info = Administrador::where('token_correo', $request->token)->first()){

            $fechaRegistro = Carbon::parse($info->token_fecha);

            $tiempoSumado = $fechaRegistro->addMinute(60)->format('Y-m-d H:i:s');
            $fechaHoy = Carbon::now('America/El_Salvador')->format('Y-m-d H:i:s');

            $d1 = new DateTime($tiempoSumado);
            $d2 = new DateTime($fechaHoy);

            if ($d1 > $d2){

                // ACTUALIZAR CONTRASENA

                Administrador::where('id', $info->id)
                    ->update(['password' => Hash::make($request->contrasena),
                        'token_correo' => null,
                        'token_fecha' => null]);

                return ['success' => 1];

            }else {
                // YA NO PUEDE CAMBIAR LA CONTRASEÑA, TIEMPO EXPIRADO

                return ['success' => 2];
            }
        }else{
            // token no encontrado
            return ['success' => 2];
        }

    }
}
