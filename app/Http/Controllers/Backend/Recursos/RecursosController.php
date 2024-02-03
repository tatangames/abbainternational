<?php

namespace App\Http\Controllers\Backend\Recursos;

use App\Http\Controllers\Controller;
use App\Models\ImagenesDelDia;
use App\Models\ImagenPreguntas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class RecursosController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de pais
    public function indexImagenesDelDia(){
        return view('backend.admin.recursos.imagenesdia.vistaimagendia');
    }


    // regresa tabla listado
    public function tablaImagenesDelDia(){
        $listado = ImagenesDelDia::orderBy('posicion', 'ASC')->get();

        foreach ($listado as $dato){
            $fechaFormat = date("d-m-Y", strtotime($dato->fecha));
            $dato->fechaFormat = $fechaFormat;
        }

        return view('backend.admin.recursos.imagenesdia.tablaimagendia', compact('listado'));
    }

    public function actualizarPosicionImagenDia(Request $request){

        $tasks = ImagenesDelDia::all();

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


    public function nuevaImagenDia(Request $request)
    {
        $rules = array(
            'descripcion' => 'required',
        );

        // imagen

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if ($request->hasFile('imagen')) {

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if ($upload) {

                if($info = ImagenesDelDia::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $nuevaImagen = new ImagenesDelDia();
                $nuevaImagen->descripcion = $request->descripcion;
                $nuevaImagen->imagen = $nombreFoto;
                $nuevaImagen->fecha = Carbon::now('America/El_Salvador');
                $nuevaImagen->posicion = $nuevaPosicion;
                $nuevaImagen->save();

                return ['success' => 1];

            } else {
                // error al subir imagen
                return ['success' => 99];
            }
        } else {
            // imagen no encontrada
            return ['success' => 99];
        }
    }


    public function borrarImagenDia(Request $request){

        $rules = array(
            'idimagen' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoImagen = ImagenesDelDia::where('id', $request->idimagen)->first()){

            $imagenOld = $infoImagen->imagen;

            if(Storage::disk('archivos')->exists($imagenOld)){
                Storage::disk('archivos')->delete($imagenOld);
            }

            ImagenesDelDia::where('id', $request->idimagen)->delete();

            // imagen fue borrada
            return ['success' => 1];
        }else{
            // decir que imagen fue borrada
            return ['success' => 1];
        }
    }




    //****************************** IMAGENES PREGUNTAS ****************************************






    public function indexImagenesPreguntas(){
        return view('backend.admin.recursos.imagenpreguntas.vistaimagenpregunta');
    }


    // regresa tabla listado
    public function tablaImagenesPreguntas(){
        $listado = ImagenPreguntas::orderBy('id', 'ASC')->get();

        return view('backend.admin.recursos.imagenpreguntas.tablaimagenpregunta', compact('listado'));
    }


    public function nuevaImagenPregunta(Request $request)
    {
        $rules = array(
            'nombre' => 'required',
        );

        // imagen

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if ($request->hasFile('imagen')) {

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if ($upload) {

                $nuevaImagen = new ImagenPreguntas();
                $nuevaImagen->imagen = $nombreFoto;
                $nuevaImagen->nombre = $request->nombre;
                $nuevaImagen->save();

                return ['success' => 1];

            } else {
                // error al subir imagen
                return ['success' => 99];
            }
        } else {
            // imagen no encontrada
            return ['success' => 99];
        }
    }


    public function informacionImagenPregunta(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = ImagenPreguntas::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }




    public function actualizarImagenPregunta(Request $request)
    {
        $rules = array(
            'id' => 'required',
            'nombre' => 'required',
        );

        // imagen

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if ($request->hasFile('imagen')) {

            if($infoImagen = ImagenPreguntas::where('id', $request->id)->first()){

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->imagen->getClientOriginalExtension();
                $nombreFoto = $nombre . strtolower($extension);
                $avatar = $request->file('imagen');
                $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

                if ($upload) {

                    $imagenOld = $infoImagen->imagen;

                    ImagenPreguntas::where('id', $infoImagen->id)
                        ->update([
                            'nombre' => $request->nombre,
                            'imagen' => $nombreFoto
                        ]);

                    if(Storage::disk('archivos')->exists($imagenOld)){
                        Storage::disk('archivos')->delete($imagenOld);
                    }

                    return ['success' => 1];

                } else {
                    // error al subir imagen
                    return ['success' => 99];
                }
            }else{

                // fila no encontrada
                return ['success' => 99];
            }
        } else {

            // solo actualizar nombre

            ImagenPreguntas::where('id', $request->id)
                ->update([
                    'nombre' => $request->nombre,
                ]);

            return ['success' => 1];
        }
    }









}
