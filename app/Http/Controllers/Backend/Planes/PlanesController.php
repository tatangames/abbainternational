<?php

namespace App\Http\Controllers\Backend\Planes;

use App\Http\Controllers\Controller;
use App\Models\IdiomaPlanes;
use App\Models\Planes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PlanesController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de pais
    public function indexPlanes(){
        return view('backend.admin.devocional.planes.vistaplanes');
    }


    // regresa tabla listado de paises
    public function tablaPlanes(){

        return "hola";

        return view('backend.admin.devocional.planes.vistaplanes', compact('listado'));
    }

    // retorna vista para agregar nuevo plan
    public function indexNuevoPlan(){

        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        return view('backend.admin.devocional.planes.nuevoplan.vistanuevoplan', compact('arrayIdiomas'));
    }



}
