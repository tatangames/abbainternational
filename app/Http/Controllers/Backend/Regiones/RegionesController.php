<?php

namespace App\Http\Controllers\Backend\Regiones;

use App\Http\Controllers\Controller;
use App\Models\Departamentos;
use App\Models\Iglesias;
use App\Models\Pais;
use App\Models\ZonaHoraria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegionesController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de pais
    public function indexPais(){
        return view('backend.admin.regiones.pais.vistapais');
    }


    // regresa tabla listado de paises
    public function tablaPais(){
        $listado = Pais::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.regiones.pais.tablapais', compact('listado'));
    }

    // registrar nuevo pais
    public function nuevoPais(Request $request){

        $rules = array(
            'nombre' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new Pais();
            $nuevo->nombre = $request->nombre;
            $nuevo->save();

            // registrado correctamente
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }


    // informacion con el ID de un pais
    public function informacionPais(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = Pais::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // actualizar con el ID de un pais
    public function actualizarPais(Request $request){
        $rules = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(Pais::where('id', $request->id)->first()){

            Pais::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




    // ********************************* DEPARTAMENTOS *******************************************************


    // regresa vista con los departamentos
    public function indexDepartamentos($idpais){

        $infoPais = Pais::where('id', $idpais)->first();
        $nombrePais = $infoPais->nombre;

        return view('backend.admin.regiones.departamentos.vistadepartamentos', compact('idpais', 'nombrePais'));
    }

    // regresa tabla con los departamentos
    public function tablaDepartamentos($idpais){

        $listado = Departamentos::where('id_pais', $idpais)
            ->orderBy('nombre', 'ASC')
            ->get();

        return view('backend.admin.regiones.departamentos.tabladepartamentos', compact('listado'));
    }

    // registrar nuevo departamento
    public function nuevoDepartamento(Request $request){

        $rules = array(
            'idpais' => 'required',
            'nombre' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new Departamentos();
            $nuevo->id_pais = $request->idpais;
            $nuevo->nombre = $request->nombre;
            $nuevo->save();

            // registrado correctamente
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }


    // informacion con el ID de un departamento
    public function informacionDepartamento(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = Departamentos::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // actualizar con el ID de un departamento
    public function actualizarDepartamento(Request $request){
        $rules = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(Departamentos::where('id', $request->id)->first()){

            Departamentos::where('id', $request->id)->update([
                'nombre' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }



    // ********************************* ZONA HORARIA *******************************************************

    // regresa vista con las zonas horarias
    public function indexZonaHoraria($idpais){

        $infoPais = Pais::where('id', $idpais)->first();
        $nombrePais = $infoPais->nombre;

        return view('backend.admin.regiones.zonahoraria.vistazonahoraria', compact('idpais', 'nombrePais'));
    }

    // regresa tabla con las zonas horarias
    public function tablaZonaHoraria($idpais){

        $listado = ZonaHoraria::where('id_pais', $idpais)
            ->orderBy('zona', 'ASC')
            ->get();

        return view('backend.admin.regiones.zonahoraria.tablazonahoraria', compact('listado'));
    }

    // registrar nueva zona horaria
    public function nuevoZonaHoraria(Request $request){

        $rules = array(
            'idpais' => 'required',
            'nombre' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new ZonaHoraria();
            $nuevo->id_pais = $request->idpais;
            $nuevo->zona = $request->nombre;
            $nuevo->save();

            // registrado correctamente
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }


    // informacion con el ID de un zona horaria
    public function informacionZonaHoraria(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if($lista = ZonaHoraria::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // actualizar con el ID de un zona horaria
    public function actualizarZonaHoraria(Request $request){
        $rules = array(
            'id' => 'required',
            'nombre' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(ZonaHoraria::where('id', $request->id)->first()){

            ZonaHoraria::where('id', $request->id)->update([
                'zona' => $request->nombre
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }



    // ********************************* IGLESIAS  *******************************************************

    // regresa vista con las iglesias
    public function indexIglesia($iddepa){

        $infoDepartamento = Departamentos::where('id', $iddepa)->first();
        $infoPais = Pais::where('id', $infoDepartamento->id_pais)->first();

        $nombrePais = $infoPais->nombre;
        $nombreDepa = $infoDepartamento->nombre;

        $arrayZonaH = ZonaHoraria::where('id_pais', $infoPais->id)->get();

        return view('backend.admin.regiones.iglesias.vistaiglesia', compact('iddepa', 'nombrePais',
            'nombreDepa', 'arrayZonaH'));
    }

    // regresa tabla con las iglesias
    public function tablaIglesia($iddepa){

        $listado = Iglesias::where('id_departamento', $iddepa)
            ->orderBy('nombre', 'ASC')
            ->get();

        foreach ($listado as $dato){
            $infoZonaH = ZonaHoraria::where('id', $dato->id_zona_horaria)->first();
            $dato->zona = $infoZonaH->zona;
        }

        return view('backend.admin.regiones.iglesias.tablaiglesia', compact('listado'));
    }


    // registrar nueva iglesia
    public function nuevaIglesia(Request $request){

        $rules = array(
            'nombre' => 'required',
            'iddepa' => 'required',
            'idzona' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new Iglesias();
            $nuevo->nombre = $request->nombre;
            $nuevo->id_departamento = $request->iddepa;
            $nuevo->id_zona_horaria = $request->idzona;
            $nuevo->save();

            // registrado correctamente
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }

    // informacion de una iglesia
    public function informacionIglesia(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($infoIglesia = Iglesias::where('id', $request->id)->first()){

            $arrayZonasH = ZonaHoraria::orderBy('zona', 'ASC')->get();

            return ['success' => 1, 'info' => $infoIglesia, 'listado' => $arrayZonasH];
        }else{
            return ['success' => 2];
        }
    }

    // actualizar datos de una iglesia
    public function actualizarIglesia(Request $request){
        $rules = array(
            'id' => 'required',
            'nombre' => 'required',
            'idzona' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(Iglesias::where('id', $request->id)->first()){

            Iglesias::where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'id_zona_horaria' => $request->idzona
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


}
