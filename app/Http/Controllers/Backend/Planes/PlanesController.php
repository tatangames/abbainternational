<?php

namespace App\Http\Controllers\Backend\Planes;

use App\Http\Controllers\Controller;
use App\Models\IdiomaPlanes;
use App\Models\Planes;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBloques;
use App\Models\PlanesBloquesTextos;
use App\Models\PlanesTextos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $listado = Planes::orderBy('posicion', 'ASC')->get();
        foreach ($listado as $dato){

            // siempre habra ididoma espanol
            $infoTituloEspanol = PlanesTextos::where('id_planes', $dato->id)
                ->where('id_idioma_planes', 1)
                ->first();

            $dato->titulo = $infoTituloEspanol->titulo;

            $fechaFormat = date("Y-m-d", strtotime($dato->fecha));

            $dato->fecha = $fechaFormat;
        }

        return view('backend.admin.devocional.planes.tablaplanes', compact('listado'));
    }

    // retorna vista para agregar nuevo plan
    public function indexNuevoPlan(){

        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $fechaCarbon = Carbon::now('America/El_Salvador');
        $fechaActual = date("Y-m-d", strtotime($fechaCarbon));

        return view('backend.admin.devocional.planes.nuevoplan.vistanuevoplan', compact('arrayIdiomas', 'fechaActual'));
    }


    public function guardarNuevoPlan(Request $request)
    {
        $regla = array(
            'fecha' => 'required',
        );

        // imagen
        // imagenportada

        // array: infoIdIdioma, infoTitulo, infoSubtitulo, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        // GUARDAR IMAGEN
        $cadena = Str::random(15);
        $tiempo = microtime();
        $union = $cadena . $tiempo;
        $nombre = str_replace(' ', '_', $union);

        $extension = '.' . $request->imagen->getClientOriginalExtension();
        $nombreFoto = $nombre . strtolower($extension);
        $avatar = $request->file('imagen');
        $upload1 = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

    // GUARDAR IMAGEN PORTADA
        $cadenaPortada = Str::random(15);
        $tiempoPortada = microtime();
        $unionPortada = $cadenaPortada . $tiempoPortada;
        $nombrePortada = str_replace(' ', '_', $unionPortada);

        $extensionPortada = '.' . $request->imagenportada->getClientOriginalExtension();
        $nombreFotoPortada = $nombrePortada . strtolower($extensionPortada);
        $avatarPortada = $request->file('imagenportada');
        $upload2 = Storage::disk('archivos')->put($nombreFotoPortada, \File::get($avatarPortada));

        if($upload1 && $upload2){

            DB::beginTransaction();
            try {

                if($info = Planes::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $nuevoPlan = new Planes();
                $nuevoPlan->imagen = $nombreFoto;
                $nuevoPlan->imagenportada = $nombreFotoPortada;
                $nuevoPlan->visible = 0; // falta los bloques
                $nuevoPlan->posicion = $nuevaPosicion;
                $nuevoPlan->fecha = Carbon::now('America/El_Salvador');
                $nuevoPlan->save();


                $datosContenedor = json_decode($request->contenedorArray, true);

                // sus idiomas
                foreach ($datosContenedor as $filaArray) {

                    // comprobar que no exte el mismo idioma
                    if(PlanesTextos::where('id_planes', $nuevoPlan->id)
                        ->where('id_idioma_planes', $filaArray['infoIdIdioma'])
                        ->first()){

                        // idioma repetido, no hacer nada

                    }else{
                        $detalle = new PlanesTextos();
                        $detalle->id_planes = $nuevoPlan->id;
                        $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                        $detalle->titulo = $filaArray['infoTitulo'];
                        $detalle->subtitulo = $filaArray['infoSubtitulo'];
                        $detalle->descripcion = $filaArray['infoDescripcion'];
                        $detalle->save();
                    }
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


    public function actualizarPosicionPlanes(Request $request){

        $tasks = Planes::all();

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


    public function indexEditarPlan($idplan)
    {
        $infoPlan = Planes::where('id', $idplan)->first();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayPlanTextos = PlanesTextos::where('id_planes', $idplan)
            ->orderBy('id_idioma_planes', 'ASC')
            ->get();

        $contador = 0;
        foreach ($arrayPlanTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.devocional.planes.editar.vistaeditarplan', compact('infoPlan', 'arrayIdiomas', 'arrayPlanTextos', 'idplan'));
    }


    public function actualizarPlanes(Request $request)
    {
        $regla = array(
            'idplan' => 'required',
            'fecha' => 'required',
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo, infoSubtitulo, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

            DB::beginTransaction();

            try {

                Planes::where('id', $request->idplan)->update([
                    'fecha' => $request->fecha,
                ]);

                $datosContenedor = json_decode($request->contenedorArray, true);

                // sus idiomas
                foreach ($datosContenedor as $filaArray) {


                    // comprobar si existe para actualizar o crear segun idioma nuevo
                    if($infoPlanTexto = PlanesTextos::where('id', $filaArray['infoIdPlanTexto'])->first()){

                        // actualizar
                        PlanesTextos::where('id', $infoPlanTexto->id)->update([
                            'titulo' => $filaArray['infoTitulo'],
                            'subtitulo' => $filaArray['infoSubtitulo'],
                            'descripcion' => $filaArray['infoDescripcion'],
                        ]);

                    }else{

                        // como no encontro, se creara

                        $detalle = new PlanesTextos();
                        $detalle->id_planes = $request->idplan;
                        $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                        $detalle->titulo = $filaArray['infoTitulo'];
                        $detalle->subtitulo = $filaArray['infoSubtitulo'];
                        $detalle->descripcion = $filaArray['infoDescripcion'];
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

    // actualizar solo imagen de plan
    public function actualizarImagenPlanes(Request $request)
    {
        $rules = array(
            'idplan' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoPlan = Planes::where('id', $request->idplan)->first()){

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

                $imagenOld = $infoPlan->imagen;

                if(Storage::disk('archivos')->exists($imagenOld)){
                    Storage::disk('archivos')->delete($imagenOld);
                }

                Planes::where('id', $request->idplan)->update([
                    'imagen' => $nombreFoto,
                ]);

                return ['success' => 1];
            }else{
                return ['success' => 99];
            }

        }else{
            // decir que imagen fue borrada
            return ['success' => 1];
        }
    }


    // actualizar solo imagen portada de plan
    public function actualizarImagenPortadaPlanes(Request $request)
    {
        $rules = array(
            'idplan' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoPlan = Planes::where('id', $request->idplan)->first()){

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

                $imagenOld = $infoPlan->imagenportada;

                if(Storage::disk('archivos')->exists($imagenOld)){
                    Storage::disk('archivos')->delete($imagenOld);
                }

                Planes::where('id', $request->idplan)->update([
                    'imagenportada' => $nombreFoto,
                ]);

                return ['success' => 1];
            }else{
                return ['success' => 99];
            }

        }else{
            // decir que imagen fue borrada
            return ['success' => 1];
        }
    }


    public function indexPlanBloque($idplan)
    {
        // texto idioma por defecto
        $infoPlanTexto = PlanesTextos::where('id_planes', $idplan)
            ->where('id_idioma_planes', 1)
            ->first();

        $nombreDevo = $infoPlanTexto->titulo;

        return view('backend.admin.devocional.planes.bloques.vistaplanesbloques', compact('nombreDevo', 'idplan'));
    }


    public function tablaPlanBloque($idplan)
    {
        $listado = PlanesBloques::where('id_planes', $idplan)->orderBy('fecha_inicio', 'ASC')->get();

        foreach ($listado as $dato){
            $fechaFormat = date("d-m-Y", strtotime($dato->fecha_inicio));
            $dato->fechaFormat = $fechaFormat;

            $textoPersonalizado = "";
            if($texto = PlanesBloquesTextos::where('id_planes_bloques', $dato->id)
                ->where('id_idioma_planes', 1)
                ->first()){
                $textoPersonalizado = $texto->titulo;
            }

            $dato->textoPersonalizado = $textoPersonalizado;
        }

        return view('backend.admin.devocional.planes.bloques.tablaplanesbloques', compact('listado'));
    }


    public function indexNuevoPlanBloque($idplan)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $fechaCarbon = Carbon::now('America/El_Salvador');
        $fechaActual = date("Y-m-d", strtotime($fechaCarbon));

        return view('backend.admin.devocional.planes.bloques.nuevo.vistanuevoplanbloque', compact('idplan', 'fechaActual', 'arrayIdiomas'));
    }


    public function registrarPlanesBloques(Request $request){

        $regla = array(
            'idplan' => 'required',
            'fecha' => 'required',
            'toggle' => 'required'
        );

        // array: infoIdIdioma, infoTitulo

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            // crear un plan bloque
            $bloque = new PlanesBloques();
            $bloque->id_planes = $request->idplan;
            $bloque->fecha_inicio = $request->fecha;
            $bloque->visible = 0;
            $bloque->texto_personalizado = $request->toggle;
            $bloque->save();

            if($request->toggle == 1){

                // sus idiomas
                foreach ($datosContenedor as $filaArray) {


                    // comprobar si existe para actualizar o crear segun idioma nuevo
                    if(PlanesBloquesTextos::where('id_planes_bloques', $bloque->id)
                        ->where('id_idioma_planes', $filaArray['infoIdIdioma'])
                        ->first()){

                        // no registrar porque ya esta registrado
                    }else{
                        // como no encontro, se creara

                        $detalle = new PlanesBloquesTextos();
                        $detalle->id_planes_bloques = $bloque->id;
                        $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                        $detalle->titulo = $filaArray['infoTitulo'];
                        $detalle->save();
                    }
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


    public function indexEditarPlanBloque($idplanbloque){


        $infoBloque = PlanesBloques::where('id', $idplanbloque)->first();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayPlanBloqueTextos = PlanesBloquesTextos::where('id_planes_bloques', $idplanbloque)
            ->orderBy('id_idioma_planes', 'ASC')
            ->get();

        $contador = 0;
        foreach ($arrayPlanBloqueTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.devocional.planes.bloques.editar.vistaeditarplanbloque', compact('infoBloque', 'arrayIdiomas', 'idplanbloque', 'arrayPlanBloqueTextos'));
    }



    public function actualizarPlanesBloques(Request $request){

        $regla = array(
            'idplanbloque' => 'required',
            'fecha' => 'required',
            'toggle' => 'required'
        );

        // array: infoIdBloqueTexto, infoIdIdioma, infoTitulo

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            PlanesBloques::where('id', $request->idplanbloque)->update([
                'fecha_inicio' => $request->fecha,
                'texto_personalizado' => $request->toggle
            ]);

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoBloqueTexto = PlanesBloquesTextos::where('id', $filaArray['infoIdBloqueTexto'])->first()){

                    // actualizar
                    PlanesBloquesTextos::where('id', $infoBloqueTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new PlanesBloquesTextos();
                    $detalle->id_planes_bloques = $request->idplanbloque;
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

    // redirecciona a vista donde se agrega detalle a planbloque
    public function indexBloqueDetalle($idplanbloque){

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.vistabloquedetalle', compact('idplanbloque'));
    }

    public function tablaBloqueDetalle($idplanbloque){

        return "tablaa2";

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.tablabloquedetalle');
    }

    public function indexNuevoPlanBloqueDetalle($idplanbloque){

        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $fechaCarbon = Carbon::now('America/El_Salvador');
        $fechaActual = date("Y-m-d", strtotime($fechaCarbon));

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.nuevo.vistanuevoplanbloquedetalle', compact('arrayIdiomas', 'fechaActual', 'idplanbloque'));
    }


    public function registrarPlanesBloquesDetalle(Request $request){

        $regla = array(
            'idplanbloque' => 'required',
            'fecha' => 'required',
        );

        // array: infoIdIdioma, infoTitulo, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            if($info = PlanesBlockDetalle::where('id_planes_bloques')
            ->orderBy('posicion', 'DESC')->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }


            // crear un plan bloque
            $bloque = new PlanesBlockDetalle();
            $bloque->id_planes_bloques = $request->idplanbloque;
            $bloque->posicion = $nuevaPosicion;
            $bloque->visible = 0;
            $bloque->save();


            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para evitar duplicados o crear segun idioma nuevo
                if(PlanesBlockDetaTextos::where('id_planes_block_detalle', $bloque->id)
                    ->where('id_idioma_planes', $filaArray['infoIdIdioma'])
                    ->first()){

                    // no registrar porque ya esta creado
                }else{
                    // como no encontro, se creara

                    $detalle = new PlanesBlockDetaTextos();
                    $detalle->id_planes_block_detalle = $bloque->id;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->titulo = $filaArray['infoTitulo'];
                    $detalle->titulo_pregunta = $filaArray['infoDescripcion'];
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

}
