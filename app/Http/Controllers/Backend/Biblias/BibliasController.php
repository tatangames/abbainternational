<?php

namespace App\Http\Controllers\Backend\Biblias;

use App\Http\Controllers\Controller;
use App\Models\Biblias;
use App\Models\BibliasTextos;
use App\Models\IdiomaPlanes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BibliasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    public function vistaBiblias(){
        return view('backend.admin.biblias.vistabiblia');
    }

    public function vistaNuevaBiblia()
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        return view('backend.admin.biblias.nuevabiblia', compact('arrayIdiomas'));
    }

    public function vistaEditarBiblia($id)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $contador = 0;
        $listado = BibliasTextos::where('id_biblias', $id)->get();
        foreach ($listado as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.biblias.editarbiblia', compact('id', 'arrayIdiomas', 'listado'));
    }

    public function tablaBiblias(){

        $listado = Biblias::orderBy('posicion', 'ASC')->get();

        foreach ($listado as $dato){
            $titulo = $this->retornoTituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.biblias.tablabiblia', compact('listado'));
    }




    // REGISTRAR BIBLIA NUEVA
    public function registrarBiblia(Request $request){

        // imagen

        // array: infoIdIdioma, infoTitulo

        // GUARDAR IMAGEN
        $cadena = Str::random(15);
        $tiempo = microtime();
        $union = $cadena . $tiempo;
        $nombre = str_replace(' ', '_', $union);

        $extension = '.' . $request->imagen->getClientOriginalExtension();
        $nombreFoto = $nombre . strtolower($extension);
        $avatar = $request->file('imagen');
        $upload1 = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

        if($upload1){

            DB::beginTransaction();

            try {

                if($info = Biblias::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $registro = new Biblias();
                $registro->visible = 0;
                $registro->posicion = $nuevaPosicion;
                $registro->imagen = $nombreFoto;
                $registro->save();

                $datosContenedor = json_decode($request->contenedorArray, true);

                // VACIO
                if (empty($datosContenedor)) {
                    return ['success' => 99];
                }

                foreach ($datosContenedor as $filaArray) {

                    $detalle = new BibliasTextos();
                    $detalle->id_biblias = $registro->id;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->titulo = $filaArray['infoTitulo'];
                    $detalle->save();
                }

                // completado
                DB::commit();
                return ['success' => 1];
            }catch(\Throwable $e){
                Log::info('error: ' . $e);
                DB::rollback();
                return ['success' => 99];
            }
        }else{
            // error al subir alguna imagen
            return ['success' => 99];
        }
    }


    private function retornoTituloBiblia($idbiblia){

        $titulo = "";
        if($datos = BibliasTextos::where('id_biblias', $idbiblia)
            ->where('id_idioma_planes', 1)
            ->first()){
            $titulo = $datos->titulo;
        }

        return $titulo;
    }

    public function actualizarPosicionBiblias(Request $request){


        $tasks = Biblias::all();

        foreach ($tasks as $task) {
            $id = $task->id;

            foreach ($request->order as $order) {
                if ($order['id'] == $id) {
                    $task->update(['posicion' => $order['posicion']]);
                }
            }
        }
        return ['success' => 1];
    }

    public function actualizarBibliaImagen(Request $request){

        // GUARDAR IMAGEN
        $rules = array(
            'id' => 'required', // idbiblia
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoBiblia = Biblias::where('id', $request->id)->first()){

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if($upload){

                $imagenOld = $infoBiblia->imagen;

                if($imagenOld != null){
                    if(Storage::disk('archivos')->exists($imagenOld)){
                        Storage::disk('archivos')->delete($imagenOld);
                    }
                }


                Biblias::where('id', $request->id)->update([
                    'imagen' => $nombreFoto,
                ]);

                return ['success' => 1];
            }else{
                return ['success' => 99];
            }

        }else{
            return ['success' => 1];
        }
    }


    public function actualizarDatosBiblia(Request $request)
    {
        $regla = array(
            'id' => 'required', // id biblia
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoPlanTexto = BibliasTextos::where('id', $filaArray['infoIdPlanTexto'])->first()){

                    // actualizar
                    BibliasTextos::where('id', $infoPlanTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new BibliasTextos();
                    $detalle->id_biblias = $request->id;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->titulo = $filaArray['infoTitulo'];
                    $detalle->save();
                }
            }

            // completado y actualizado
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



    public function estadoBiblia(Request $request)
    {
        $regla = array(
            'idbiblia' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        Biblias::where('id', $request->idbiblia)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }


}
