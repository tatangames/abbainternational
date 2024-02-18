<?php

namespace App\Http\Controllers\Backend\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PlanesBlockDetaUsuario;
use App\Models\Usuarios;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function index(){


        $conteoUsuario = Usuarios::count();
        $totalDevoCompletados = PlanesBlockDetaUsuario::where('completado', 1)->count();

        return view('backend.admin.dashboard.vistadashboard', compact('conteoUsuario', 'totalDevoCompletados'));
    }



}
