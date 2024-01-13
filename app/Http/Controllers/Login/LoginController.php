<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct(){
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function index(){
        return view('frontend.login.vistalogin');
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

        //$credentials = $request->only('email', 'password');


        $credentials = request()->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials)) {
            //return redirect()->route('admin.index');
            return ['success'=> 1, 'ruta'=> route('admin.panel')];
        } else {
            return ['success' => 2];
        }




        if (Auth::attempt($credentials)) {
            // Autenticación exitosa
            return ['success'=> 1, 'ruta'=> route('admin.panel')];
        }
        return ['success' => 2]; // password incorrecta

       /* if(Administrador::where('usuario', $request->usuario)->first()){
            if(Auth::attempt(['usuario' => $request->usuario, 'password' => $request->password])) {

                return ['success'=> 1, 'ruta'=> route('admin.panel')];
            }else{
                return ['success' => 2]; // password incorrecta
            }
        }else{
            return ['success' => 3]; // usuario no encontrado
        }*/
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect('/');
    }
}
