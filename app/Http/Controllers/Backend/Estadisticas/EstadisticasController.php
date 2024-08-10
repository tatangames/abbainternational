<?php

namespace App\Http\Controllers\Backend\Estadisticas;

use App\Http\Controllers\Controller;
use App\Models\PlanesBlockDetaUsuarioTotal;
use App\Models\PlanesBloques;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function indexEstadisticas()
    {

        $totalDevoHechosUsuario = $this->totalUsuariosHaciendoDevocional();
        $arrayPorEdades = $this->totalUsuariosPorEdades();
        $totalMenores = $arrayPorEdades['menores'];
        $totalMayores = $arrayPorEdades['mayores'];


        return view('backend.admin.estadisticas.vistaestadisticas', compact('totalDevoHechosUsuario',
        'totalMenores', 'totalMayores'));
    }


    // TOTAL DE USUARIO QUE HAN HECHO DEVOCIONALES
    private function totalUsuariosHaciendoDevocional()
    {
        // OBTENER TOTAL DE USUARIOS QUE HAN HECHO POR LO MENOS 1 DEVOCIONAL

        $datos = PlanesBlockDetaUsuarioTotal::select('id_usuario')
            ->groupBy('id_usuario')
            ->get();

        $conteo = 0;
        foreach ($datos as $item){
            $conteo++;
        }

        return $conteo;
    }


    // TOTAL DE USUARIOS POR EDADES
    // MENOR A 30 ANIOS
    // MAYOR A 30 ANIOS
    private function totalUsuariosPorEdades()
    {
        // usuarios que tienen edad de maximo 30 anios, cuantos estan haciendo devocional

        $menoresA30 = DB::table('planes_blockdeta_usertotal AS pb')
            ->join('usuarios AS u', 'pb.id_usuario', '=', 'u.id')
            ->select('u.id')
            ->whereDate('u.fecha_nacimiento', '>', Carbon::now()->subYears(30))
            ->groupBy('u.id')
            ->get();

        $conteoMenores = 0;
        foreach ($menoresA30 as $item){
            $conteoMenores++;
        }

        $mayoresA30 = DB::table('planes_blockdeta_usertotal AS pb')
            ->join('usuarios AS u', 'pb.id_usuario', '=', 'u.id')
            ->select('u.id')
            ->whereDate('u.fecha_nacimiento', '<=', Carbon::now()->subYears(30))
            ->groupBy('u.id')
            ->get();

        $conteoMayores = 0;

        foreach ($mayoresA30 as $item){
            $conteoMayores++;
        }

        return ['menores' => $conteoMenores, 'mayores' => $conteoMayores];
/*

        $arrayMenor = PlanesBlockDetaUsuarioTotal::where('')
        select('id_usuario')
            ->groupBy('id_usuario')
            ->get();

        $conteo = 0;
        foreach ($datos as $item){
            $conteo++;
        }*/

        return $conteo;
    }


}
