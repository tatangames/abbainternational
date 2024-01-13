<?php

namespace App\Http\Controllers\Controles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlRolController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function indexRedireccionamiento(){

        $user = Auth::user();

        // ADMINISTRADOR
        if($user->hasRole('Admin')){
            $ruta = 'admin.dashboard.index';
        }
        else{
            $ruta = 'no.permisos.index';
        }



        return view('backend.index', compact( 'ruta', 'user'));
    }

    public function indexSinPermiso(){
        return view('errors.403');
    }
}
