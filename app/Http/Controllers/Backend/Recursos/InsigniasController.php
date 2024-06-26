<?php

namespace App\Http\Controllers\Backend\Recursos;

use App\Http\Controllers\Controller;
use App\Models\IdiomaPlanes;
use App\Models\InsigniasTextos;
use App\Models\NivelesInsignias;
use App\Models\TipoInsignias;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InsigniasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }


    public function indexTipoInsignias(){
        return view('backend.admin.recursos.insignias.vistatipoinsignias');
    }

    public function tablaTipoInsignias(){

        $listado = TipoInsignias::orderBy('id', 'ASC')->get();

        foreach ($listado as $dato){

            $infoTexto = InsigniasTextos::where('id_idioma_planes', 1)
                ->where('id_tipo_insignia', $dato->id)
                ->first();

            $dato->titulo = $infoTexto->texto_1;
        }

        return view('backend.admin.recursos.insignias.tablatipoinsignias', compact('listado'));
    }


    public function indexVistaNuevoTipoInsignias(){
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        return view('backend.admin.recursos.insignias.nuevo.vistanuevotipoinsignias', compact('arrayIdiomas'));
    }


    public function registrarTipoInsignia(Request $request){

        // imagen
        // array: infoIdIdioma, infoTitulo, infoSubtitulo

        // GUARDAR IMAGEN
        $cadena = Str::random(15);
        $tiempo = microtime();
        $union = $cadena . $tiempo;
        $nombre = str_replace(' ', '_', $union);

        $extension = '.' . $request->imagen->getClientOriginalExtension();
        $nombreFoto = $nombre . strtolower($extension);
        $avatar = $request->file('imagen');
        $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

        if($upload){

            DB::beginTransaction();

            try {

                $nuevo = new TipoInsignias();
                $nuevo->imagen = $nombreFoto;
                $nuevo->visible = 1;
                $nuevo->save();
                $datosContenedor = json_decode($request->contenedorArray, true);

                // sus idiomas
                foreach ($datosContenedor as $filaArray) {

                    $detalle = new InsigniasTextos();
                    $detalle->id_tipo_insignia = $nuevo->id;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->texto_1 = $filaArray['infoTitulo'];
                    $detalle->texto_2 = $filaArray['infoSubtitulo'];
                    $detalle->save();
                }

                // guardar por defecto nivel 1
                $nuevoNivel = new NivelesInsignias();
                $nuevoNivel->id_tipo_insignia = $nuevo->id;
                $nuevoNivel->nivel = 1;
                $nuevoNivel->save();

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




    // ********************************************************************************************


    public function indexVistaEditarTipoInsignia($idtipoinsignia){

        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $arrayInsigniaTexto = InsigniasTextos::where('id_tipo_insignia', $idtipoinsignia)->get();

        $contador = 0;
        foreach ($arrayInsigniaTexto as $dato){
            $contador++;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;

            $dato->contador = $contador;
        }

        $infoTipoInsignia = TipoInsignias::where('id', $idtipoinsignia)->first();
        $visible = $infoTipoInsignia->visible;

        return view('backend.admin.recursos.insignias.editar.vistaeditartipoinsignias', compact('idtipoinsignia',
            'arrayIdiomas', 'arrayInsigniaTexto', 'visible'));
    }

    public function actualizarImagen(Request $request){

        $rules = array(
            'idtipoinsignia' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoInsignia = TipoInsignias::where('id', $request->idtipoinsignia)->first()){

            // siempre viene imagen

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if($upload){

                $imagenOld = $infoInsignia->imagen;

                if(Storage::disk('archivos')->exists($imagenOld)){
                    Storage::disk('archivos')->delete($imagenOld);
                }

                TipoInsignias::where('id', $request->idtipoinsignia)->update([
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


    public function actualizarTiposInsiginias(Request $request)
    {
        $regla = array(
            'idtipoinsignia' => 'required',
        );

        // array: infoIdInsigniaTexto, infoIdIdioma, infoTitulo, infoSubtitulo

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoTexto = InsigniasTextos::where('id', $filaArray['infoIdInsigniaTexto'])->first()){

                    // actualizar
                    InsigniasTextos::where('id', $infoTexto->id)->update([
                        'texto_1' => $filaArray['infoTitulo'],
                        'texto_2' => $filaArray['infoSubtitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new InsigniasTextos();
                    $detalle->id_tipo_insignia = $request->idtipoinsignia;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->titulo = $filaArray['infoTitulo'];
                    $detalle->subtitulo = $filaArray['infoSubtitulo'];
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


    public function indexVistaInsigniaNiveles($idtipoinsignia){
        return view('backend.admin.recursos.insignias.niveles.vistanivelesinsignias', compact('idtipoinsignia'));
    }

    public function tablaVistaInsigniaNiveles($idtipoinsignia){

        $listado = NivelesInsignias::where('id_tipo_insignia', $idtipoinsignia)
            ->orderBy('nivel', 'ASC')
            ->get();

        return view('backend.admin.recursos.insignias.niveles.tablanivelesinsignias', compact('listado'));
    }


    public function registrarNuevoNivel(Request $request){

        $regla = array(
            'nivel' => 'required',
            'idtipoinsignia' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(NivelesInsignias::where('id_tipo_insignia', $request->idtipoinsignia)
            ->where('nivel', $request->nivel)->first()){
            // solo decir que fue registrado
            return ['success' => 1];
        }else{


            // verificar que no sea igual o menor a algun niveles repetidos.

            $numeroMayor = NivelesInsignias::where('id_tipo_insignia', $request->idtipoinsignia)
                ->max('nivel');

            // no se puede registrar un numero igual o menor a los niveles ya registrados
            if($request->nivel <= $numeroMayor){
                return ['success' => 2];
            }


            $nuevo = new NivelesInsignias();
            $nuevo->id_tipo_insignia = $request->idtipoinsignia;
            $nuevo->nivel = $request->nivel;
            $nuevo->save();

            return ['success' => 3];
        }
    }




}
