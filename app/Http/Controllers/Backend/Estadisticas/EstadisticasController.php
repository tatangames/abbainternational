<?php

namespace App\Http\Controllers\Backend\Estadisticas;

use App\Http\Controllers\Controller;
use App\Models\Planes;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaUsuario;
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

        $totalPor3Dias = $this->totalDevoPor3Dias();
        $totalPor7Dias = $this->totalDevoPor7Dias();


        return view('backend.admin.estadisticas.vistaestadisticas', compact('totalDevoHechosUsuario',
        'totalMenores', 'totalMayores', 'totalPor3Dias', 'totalPor7Dias'));
    }


    // TOTAL DE USUARIO QUE HAN HECHO DEVOCIONALES
    private function totalUsuariosHaciendoDevocional()
    {
        // OBTENER TOTAL DE USUARIOS QUE HAN HECHO POR LO MENOS 1 DEVOCIONAL

        $datos = PlanesBlockDetaUsuario::select('id_usuario')
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
    }


    // USUARIOS HAN HECHO DEVOCIONAL DE 1 A 3 DIAS

    private function totalDevoPor3Dias()
    {

        $pilaIdUsuarios = array();
        $arrayPlanes = Planes::all();

        foreach ($arrayPlanes as $dato){

            // FILTRADO PRIMEROS 3 DIAS

            $arrayPlanBloque = PlanesBloques::where('id_planes', $dato->id)
                ->orderBy('fecha_inicio', 'ASC')
                ->take('3')
                ->get();

            foreach ($arrayPlanBloque as $jj){

                // SIEMPRE VERIFICAR QUE SE ENCUENTRE Y EL CON PRIMERO ES SUFICIENTE
                if($filaPlanBloqueDetalle = PlanesBlockDetalle::where('id_planes_bloques', $jj->id)
                    ->first()){

                    // el usuario este si hizo devocional al menos dentro del rango de 1 - 3 dias
                    $filaInfo = PlanesBlockDetaUsuario::where('id_planes_block_deta', $filaPlanBloqueDetalle->id)->get();

                    foreach ($filaInfo as $rr){
                        array_push($pilaIdUsuarios, $rr->id_usuario);
                    }
                }
            }
        }



        $totalConteo = 0;
        $idsUnicos = collect($pilaIdUsuarios)->unique()->values()->all();

        foreach ($idsUnicos as $dato){
            $totalConteo++;
        }

        return $totalConteo;
    }


    // USUARIOS HAN HECHO DEVOCIONAL DE 4 A 7 DIAS

    private function totalDevoPor7Dias()
    {

        $pilaIdUsuarios = array();
        $arrayPlanes = Planes::all();

        foreach ($arrayPlanes as $dato){

            // FILTRADO PRIMEROS 7 DIAS

            $arrayPlanBloque = PlanesBloques::where('id_planes', $dato->id)
                ->orderBy('fecha_inicio', 'ASC')
                ->offset(3) // Ignorar las primeras 3 filas
                ->limit(4) // Opcional: limitar la cantidad de resultados
                ->get();

            foreach ($arrayPlanBloque as $jj){

                // SIEMPRE VERIFICAR QUE SE ENCUENTRE Y EL CON PRIMERO ES SUFICIENTE
                if($filaPlanBloqueDetalle = PlanesBlockDetalle::where('id_planes_bloques', $jj->id)
                    ->first()){

                    // el usuario este si hizo devocional al menos dentro del rango de 1 - 3 dias
                    $filaInfo = PlanesBlockDetaUsuario::where('id_planes_block_deta', $filaPlanBloqueDetalle->id)->get();

                    foreach ($filaInfo as $rr){
                        array_push($pilaIdUsuarios, $rr->id_usuario);
                    }
                }
            }
        }

        $totalConteo = 0;
        $idsUnicos = collect($pilaIdUsuarios)->unique()->values()->all();

        foreach ($idsUnicos as $dato){
            $totalConteo++;
        }

        return $totalConteo;
    }

}
