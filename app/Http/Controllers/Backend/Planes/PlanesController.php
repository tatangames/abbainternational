<?php

namespace App\Http\Controllers\Backend\Planes;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionarioTextos;
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


    public function activacionPlan(Request $request)
    {
        $regla = array(
            'idplan' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        // VERIFICAR QUE HAYA BLOQUES EN TABLA:
        if(PlanesBloques::where('id_planes', $request->idplan)->first()){

            // si hay bloque creado
            Planes::where('id', $request->idplan)->update([
                'visible' => $request->estado,
            ]);



            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
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

            // Evitar bloques que sean de la misma fecha a los ya registrados
            if(PlanesBloques::where('id_planes', $request->idplan)
                ->whereDate('fecha_inicio', $request->fecha)->first()){
                return ['success' => 1];
            }



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
            return ['success' => 2];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function activacionPlanBloque(Request $request)
    {
        $regla = array(
            'idplanbloques' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        // VERIFICAR QUE HAYA PLAN BLOQUES DETALLE EN TABLA:
        if(PlanesBlockDetalle::where('id_planes_bloques', $request->idplanbloques)->first()){

            // si hay bloque creado
            PlanesBloques::where('id', $request->idplanbloques)->update([
                'visible' => $request->estado,
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
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

            $infoPlanBloque = PlanesBloques::where('id', $request->idplanbloque)->first();

            // Evitar bloques que sean de la misma fecha a los ya registrados
            if(PlanesBloques::where('id_planes', $infoPlanBloque->id_planes)
                ->where('id', '!=', $infoPlanBloque->id)
                ->whereDate('fecha_inicio', $request->fecha)
                ->first()){
                return ['success' => 1];
            }


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
            return ['success' => 2];
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

        $listado = PlanesBlockDetalle::where('id_planes_bloques', $idplanbloque)
            ->orderBy('posicion', 'ASC')->get();

        foreach ($listado as $dato){

            $titulo = "";
            if($info = PlanesBlockDetaTextos::where('id_planes_block_detalle', $dato->id)
                ->where('id_idioma_planes', 1)->first()){
                $titulo = $info->titulo;
            }
            $dato->titulo = $titulo;
        }

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.tablabloquedetalle', compact('listado'));
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
        );

        // array: infoIdIdioma, infoTitulo, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            if($info = PlanesBlockDetalle::where('id_planes_bloques', $request->idplanbloque)
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
            $bloque->ignorar_pregunta = 1;
            $bloque->save();


            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                $contenidoHtmlConJavascript = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $filaArray['infoDescripcion'] . "</body>
                    </html>";


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
                    $detalle->titulo_pregunta = $contenidoHtmlConJavascript;
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


    public function actualizarPosicionPlanesBlockDetalle(Request $request){

        $tasks = PlanesBlockDetalle::all();

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


    // Validar que tenga el Devocional como minimo registrado ya
    public function activacionPlanBloqueDetalle(Request $request)
    {
        $regla = array(
            'idplanbloquedetalle' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        // VERIFICAR QUE HAYA PLAN BLOQUES DETALLE EN TABLA:
        if(BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idplanbloquedetalle)->first()){

            // si hay bloque creado
            PlanesBlockDetalle::where('id', $request->idplanbloquedetalle)->update([
                'visible' => $request->estado,
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




    public function indexEditarPlanBloqueDetalle($idplanbloquedetalle){

        $infoBloque = PlanesBlockDetalle::where('id', $idplanbloquedetalle)->first();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayPlanBlockDetaTextos = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idplanbloquedetalle)
            ->orderBy('id_idioma_planes', 'ASC')
            ->get();

        $contador = 0;
        foreach ($arrayPlanBlockDetaTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }


        return view('backend.admin.devocional.planes.bloques.bloquedetalle.editar.vistaeditarplanbloquedetalle', compact('infoBloque', 'arrayIdiomas', 'idplanbloquedetalle', 'arrayPlanBlockDetaTextos'));
    }


    public function actualizarPlanesBloquesDetaTextos(Request $request)
    {



        $regla = array(
            'idplanbloquedetalle' => 'required',
            'toggle' => 'required'
        );

        // array: infoIdBloqueDetaTexto, infoIdIdioma, infoTitulo, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            // actualizar toggle
            PlanesBlockDetalle::where('id', $request->idplanbloquedetalle)->update([
                'ignorar_pregunta' => $request->toggle,
            ]);



            // TABLA: planes_block_detalle  no hay nada que actualizar

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {



                $contenidoHtmlConJavascript = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $filaArray['infoDescripcion'] . "</body>
                    </html>";



                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoBloqueDetaTexto = PlanesBlockDetaTextos::where('id', $filaArray['infoIdBloqueDetaTexto'])->first()){

                    // actualizar
                    PlanesBlockDetaTextos::where('id', $infoBloqueDetaTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                        'titulo_pregunta' => $contenidoHtmlConJavascript,
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new PlanesBlockDetaTextos();
                    $detalle->id_planes_block_detalle = $request->idplanbloquedetalle;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->titulo = $filaArray['infoTitulo'];
                    $detalle->titulo_pregunta = $contenidoHtmlConJavascript;
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



    public function indexDevocionalPregunta($idplanbloquedetalle)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $arrayCuestionario = BloqueCuestionarioTextos::where('id_bloque_detalle', $idplanbloquedetalle)
            ->orderBy('id', 'ASC')
            ->get();

        // ESTO ES PARA QUE EL SELECT AL SELECCIONAR IDIOMA ME DEJE ESPANOL POR DEFECTO
        // SI CON ES 0, YA QUE AGREGA CADA UNO DE UN SOLO A SERVIDOR
        $conteoIdioma = 0;
        if(BloqueCuestionarioTextos::where('id_bloque_detalle', $idplanbloquedetalle)->first()){
            $conteoIdioma = 1;
        }

        $contador = 0;
        foreach ($arrayCuestionario as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.devocional.vistanuevodevocionales', compact('idplanbloquedetalle',
        'arrayIdiomas', 'arrayCuestionario', 'conteoIdioma'));
    }

    // guardar devocional segun idioma
    public function guardarDevocionalTexto(Request $request)
    {


        $regla = array(
            'idblockdetalle' => 'required',
            'ididioma' => 'required',
            'devocional' => 'required',
            'titulo' => 'required',
        );


        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {
                // comprobar si existe
                if(BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idblockdetalle)
                    ->where('id_idioma_planes', $request->ididioma)
                    ->first()){

                    // no hacer nada

                }else{


                    // *************** TITULO ****************************





                    $contenidoHtmlConJavascriptTitulo = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

                    $contenidoHtmlConJavascriptTitulo .= $this->retornoFuentesCSS();


                    $contenidoHtmlConJavascriptTitulo .= "</style>
                        <script type='text/javascript'>

                            function disminuirTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var resta = fontSize - 5;
                                    if(fontSize < 5){
                                        resta = 5;
                                    }

                                    elementos[i].style.fontSize = (resta) + 'px';
                                }
                            }

                            function aumentarTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var suma = fontSize + 5;
                                    if(suma > 40){
                                        suma = 40;
                                    }
                                    elementos[i].style.fontSize = (suma) + 'px';
                                }
                            }


                        </script>
                    </head>
                    <body>" . $request->titulo . "</body>
                    </html>";





                    // ****************** DEVOCIONAL ************************



                    $contenidoHtmlDevocional = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

                    $contenidoHtmlDevocional .= $this->retornoFuentesCSS();


                    $contenidoHtmlDevocional .= "</style>
                        <script type='text/javascript'>

                            function disminuirTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var resta = fontSize - 5;
                                    if(fontSize < 5){
                                        resta = 5;
                                    }

                                    elementos[i].style.fontSize = (resta) + 'px';
                                }
                            }

                            function aumentarTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var suma = fontSize + 5;
                                    if(suma > 40){
                                        suma = 40;
                                    }
                                    elementos[i].style.fontSize = (suma) + 'px';
                                }
                            }


                        </script>
                    </head>
                    <body>" . $request->devocional . "</body>
                    </html>";



                    $contenidoHtmlTitulo = "<html>
                <body>" . $request->titulo . "</body>
                    </html>";

                    $contenidoHtml = "<html>
                <body>" . $request->devocional . "</body>
                    </html>";



                    $detalle = new BloqueCuestionarioTextos();
                    $detalle->id_bloque_detalle = $request->idblockdetalle;
                    $detalle->id_idioma_planes = $request->ididioma;
                    $detalle->texto = $contenidoHtmlDevocional;    // TEXTO DEL DEVOCIONAL, QUE SE CARGA EN UN WEBVIEW
                    $detalle->texto_dia = $contenidoHtml;  // EL MISMO TEXTO PERO ESTE SE CARGA EN TEXTVIEW
                    $detalle->titulo = $contenidoHtmlConJavascriptTitulo;  // TITULO QUE AL TOCARLO ME MANDA A A BIBLIA
                    $detalle->titulo_dia = $contenidoHtmlTitulo; // EL MISMO TEXTO DE TITULO PERO SE CARGARA EN TEXTIEW



                    $detalle->save();
                }


            // creado
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function actualizarDevocionalTexto(Request $request)
    {
        Log::info($request->all());
        $regla = array(
            'idcuestionario' => 'required',
            'devocional' => 'required',
            'titulo' => 'required'
        );


        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        if ($info = BloqueCuestionarioTextos::where('id', $request->idcuestionario)->first()){


            // *************  TITULO *******************

            $contenidoHtmlConJavascriptTitulo = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $contenidoHtmlConJavascriptTitulo .= $this->retornoFuentesCSS();


            $contenidoHtmlConJavascriptTitulo .= "</style>
                        <script type='text/javascript'>

                           function disminuirTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var resta = fontSize - 5;
                                    if(fontSize < 5){
                                        resta = 5;
                                    }

                                    elementos[i].style.fontSize = (resta) + 'px';
                                }
                            }

                            function aumentarTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var suma = fontSize + 5;
                                    if(suma > 40){
                                        suma = 40;
                                    }
                                    elementos[i].style.fontSize = (suma) + 'px';
                                }
                            }

                        </script>
                    </head>
                    <body>" . $request->titulo . "</body>
                    </html>";





            // *************  DEVOCIONAL *******************



            $contenidoHtmlDevocional= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <style>
                        ";

            $contenidoHtmlDevocional .= $this->retornoFuentesCSS();


            $contenidoHtmlDevocional .= "  </style>
                        <script type='text/javascript'>


                            function disminuirTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var resta = fontSize - 5;
                                    if(fontSize < 5){
                                        resta = 5;
                                    }

                                    elementos[i].style.fontSize = (resta) + 'px';
                                }
                            }

                            function aumentarTamano() {
                                var elementos = document.getElementsByTagName('*');
                                for (var i = 0; i < elementos.length; i++) {
                                    var estilo = window.getComputedStyle(elementos[i], null).getPropertyValue('font-size');
                                    var fontSize = parseFloat(estilo);
                                    var suma = fontSize + 5;
                                    if(suma > 40){
                                        suma = 40;
                                    }
                                    elementos[i].style.fontSize = (suma) + 'px';
                                }
                            }


                        </script>
                    </head>
                    <body>" . $request->devocional . "</body>
                    </html>";













            $contenidoHtmlTitulo = "<html>
                <body>" . $request->titulo . "</body>
                    </html>";


            $contenidoHtml = "<html>
                <body>" . $request->devocional . "</body>
                    </html>";


            // actualizar
            BloqueCuestionarioTextos::where('id', $info->id)->update([
                'texto' => $contenidoHtmlDevocional,
                'texto_dia' => $contenidoHtml,
                'titulo' => $contenidoHtmlConJavascriptTitulo,
                'titulo_dia' => $contenidoHtmlTitulo,
            ]);
        }

        return ['success' => 1];
    }


    private function retornoFuentesCSS(){

        $fuentes = "
                @font-face {
                    font-family: 'Fuente1';
                    src: url('file:///android_res/font/notosans_light.ttf') format('truetype'); /* Ruta de la tercera fuente */
                 }

                @font-face {
                    font-family: 'Fuente2';
                    src: url('file:///android_res/font/notosans_condensed_medium.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                @font-face {
                    font-family: 'Fuente3';
                    src: url('file:///android_res/font/times_new_normal_regular.ttf') format('truetype'); /* Ruta de la tercera fuente */
                }

                /* Utilizar las fuentes según sea necesario */
                .texto-fuente1 {
                    font-family: 'Fuente1', sans-serif;
                }

                .texto-fuente2 {
                    font-family: 'Fuente2', sans-serif;
                }

                .texto-fuente3 {
                    font-family: 'Fuente3', sans-serif;
                }
        ";

        return $fuentes;
    }



}
