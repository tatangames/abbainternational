<?php

namespace App\Http\Controllers\Backend\Recursos;

use App\Http\Controllers\Controller;
use App\Models\ComparteApp;
use App\Models\ComparteAppTextos;
use App\Models\IdiomaPlanes;
use App\Models\ImagenesDelDia;
use App\Models\ImagenPreguntas;
use App\Models\NotificacionTextos;
use App\Models\NotificacionUsuario;
use App\Models\Planes;
use App\Models\RecursosWeb;
use App\Models\RedesSociales;
use App\Models\TipoNotificacion;
use App\Models\TipoVideo;
use App\Models\VideosHoy;
use App\Models\VideosTextos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // imagen, imagen2

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



            $cadena2 = Str::random(15);
            $tiempo2 = microtime();
            $union2 = $cadena2 . $tiempo2;
            $nombre2 = str_replace(' ', '_', $union2);

            $extension2 = '.' . $request->imagen2->getClientOriginalExtension();
            $nombreFoto2 = $nombre2 . strtolower($extension2);
            $avatar2 = $request->file('imagen2');
            $upload2 = Storage::disk('archivos')->put($nombreFoto2, \File::get($avatar2));


            if ($upload && $upload2) {

                if($info = ImagenesDelDia::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $nuevaImagen = new ImagenesDelDia();
                $nuevaImagen->descripcion = $request->descripcion;
                $nuevaImagen->imagen = $nombreFoto;
                $nuevaImagen->imagen_ingles = $nombreFoto2;
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






    // *********************************** INFORMACION DE COMPARTIR APP  *****************************

    public function indexComparteApp()
    {
        // me traera todos, ya que solo 1 registro de comparte app hay
        $arrayComparteAppTextos = ComparteAppTextos::orderBy('id_idioma_planes', 'ASC')->get();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $contador = 0;
        foreach ($arrayComparteAppTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }



        return view('backend.admin.informacion.comparteapp.vistacomparteapp', compact('arrayComparteAppTextos', 'arrayIdiomas'));
    }


    public function actualizarImagenComparteApp(Request $request)
    {

        if($request->file('imagen')){

            $infoComparteApp = ComparteApp::where('id', 1)->first();

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if($upload){

                $imagenOld = $infoComparteApp->imagen;

                if(Storage::disk('archivos')->exists($imagenOld)){
                    Storage::disk('archivos')->delete($imagenOld);
                }

                ComparteApp::where('id', 1)->update([
                    'imagen' => $nombreFoto,
                ]);

                return ['success' => 1];
            }else{
                return ['success' => 99];
            }
        }else{
            // no trae imagen
            return ['success' => 99];
        }
    }


    public function registrarIdiomaComparteApp(Request $request)
    {
        $rules = array(
            'ididioma' => 'required',
            'titulo' => 'required',
        );

        // subtitulo

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if(ComparteAppTextos::where('id_idioma_planes', $request->ididioma)->first()){
            // no hacer nada
        }else{
            // registrar
            $nuevo = new ComparteAppTextos();
            $nuevo->id_idioma_planes = $request->ididioma;
            $nuevo->texto_1 = $request->titulo;
            $nuevo->texto_2 = $request->subtitulo;
            $nuevo->save();
        }

        return ['success' => 1];
    }


    public function actualizarComparteApp(Request $request)
    {
        $rules = array(
            'idfila' => 'required',
            'titulo' => 'required',
        );

        // subtitulo

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        ComparteAppTextos::where('id', $request->idfila)->update([
            'texto_1' => $request->titulo,
            'texto_2' => $request->subtitulo
        ]);

        return ['success' => 1];
    }


    public function indexVideosHoy()
    {
        return view('backend.admin.recursos.videoshoy.vistavideoshoy');
    }


    public function tablaVideosHoy()
    {

        $listado = VideosHoy::orderBy('posicion', 'ASC')->get();

        foreach ($listado as $dato){
            $fechaFormat = date("d-m-Y", strtotime($dato->fecha));
            $dato->fechaFormat = $fechaFormat;

            $titulo = "";
            if($infoTexto = VideosTextos::where('id_videos_hoy', $dato->id)
                ->where('id_idioma_planes', 1)
                ->first()){
                $titulo = $infoTexto->titulo;
            }

            $dato->titulo = $titulo;

            $infoTipovideo = TipoVideo::where('id', $dato->id_tipo_video)->first();
            $dato->tipovideo = $infoTipovideo->nombre;
        }


        return view('backend.admin.recursos.videoshoy.tablavideoshoy', compact('listado'));
    }


    public function vistaNuevoVideosHoy()
    {
        $arrayTipo = TipoVideo::orderBy('id', 'ASC')->get();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $fechaCarbon = Carbon::now('America/El_Salvador');
        $fechaActual = date("Y-m-d", strtotime($fechaCarbon));

        return view('backend.admin.recursos.videoshoy.nuevo.vistanuevovideohoy', compact('arrayTipo',
        'arrayIdiomas', 'fechaActual'));
    }


    public function registrarVideoUrl(Request $request)
    {

        $rules = array(
            'fecha' => 'required',
            'idtipovideo' => 'required',
            'urlvideo' => 'required',
        );

        // imagen
        //array: infoIdIdioma, infoTitulo

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { return ['success' => 0]; }

        DB::beginTransaction();

        try {

            if($request->file('imagen')){

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->imagen->getClientOriginalExtension();
                $nombreFoto = $nombre . strtolower($extension);
                $avatar = $request->file('imagen');
                $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

                if($upload){

                    $datosContenedor = json_decode($request->contenedorArray, true);


                    if($info = VideosHoy::orderBy('posicion', 'DESC')->first()){
                        $nuevaPosicion = $info->posicion + 1;
                    }else{
                        $nuevaPosicion = 1;
                    }

                    $nuevo = new VideosHoy();
                    $nuevo->id_tipo_video = $request->idtipovideo;
                    $nuevo->url_video = $request->urlvideo;
                    $nuevo->posicion = $nuevaPosicion;
                    $nuevo->fecha = $request->fecha;
                    $nuevo->imagen = $nombreFoto;
                    $nuevo->save();


                    foreach ($datosContenedor as $filaArray) {

                        $detalle = new VideosTextos();
                        $detalle->id_videos_hoy = $nuevo->id;
                        $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                        $detalle->titulo = $filaArray['infoTitulo'];
                        $detalle->save();
                    }

                    DB::commit();
                    return ['success' => 1];
                }else{
                    return ['success' => 99];
                }
            }else{
                // no trae imagen
                return ['success' => 99];
            }
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function actualizarPosicionVideosHoy(Request $request)
    {
        $tasks = VideosHoy::all();

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


    public function borrarVideoUrl(Request $request){

        $rules = array(
            'idvideohoy' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoUrl = VideosHoy::where('id', $request->idvideohoy)->first()){

            $imagenOld = $infoUrl->imagen;

            if(Storage::disk('archivos')->exists($imagenOld)){
                Storage::disk('archivos')->delete($imagenOld);
            }

            // eliminar todos los idiomas registrados
            VideosTextos::where('id_videos_hoy', $request->idvideohoy)->delete();
            VideosHoy::where('id', $request->idvideohoy)->delete();

            // borrado
            return ['success' => 1];
        }else{
            // decir que fue borrado
            return ['success' => 1];
        }
    }


    public function indexVideosHoyEditar($idvideohoy)
    {
        $infoVideo = VideosHoy::where('id', $idvideohoy)->first();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayTipo = TipoVideo::orderBy('id', 'ASC')->get();

        $arrayVideosTextos = VideosTextos::where('id_videos_hoy', $idvideohoy)->get();

        $contador = 0;
        foreach ($arrayVideosTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.recursos.videoshoy.editar.vistavideohoyeditar', compact('idvideohoy',
        'infoVideo', 'arrayIdiomas', 'arrayTipo', 'arrayVideosTextos'));
    }


    public function actualizarImagenVideosHoy(Request $request)
    {
        $rules = array(
            'idvideohoy' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoVideo = VideosHoy::where('id', $request->idvideohoy)->first()){

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

                $imagenOld = $infoVideo->imagen;

                if(Storage::disk('archivos')->exists($imagenOld)){
                    Storage::disk('archivos')->delete($imagenOld);
                }

                VideosHoy::where('id', $request->idvideohoy)->update([
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


    public function actualizarVideosHoyTextos(Request $request)
    {

        $rules = array(
            'fecha' => 'required',
            'idtipovideo' => 'required',
            'idvideoshoy' => 'required',
            'urlvideo' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        // infoIdVideoTexto, infoIdIdioma, infoTitulo


        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            VideosHoy::where('id', $request->idvideoshoy)->update([
                'fecha' => $request->fecha,
                'url_video' => $request->urlvideo,
                'id_tipo_video' => $request->idtipovideo,
            ]);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoVideoTexto = VideosTextos::where('id', $filaArray['infoIdVideoTexto'])->first()){

                    // actualizar
                    VideosTextos::where('id', $infoVideoTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new VideosTextos();
                    $detalle->id_videos_hoy = $request->idvideoshoy;
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





    //****************** NOTIFICACIONES  *****************************************


    public function indexNotificacion(){
        return view('backend.admin.recursos.notificacion.vistanotificacion');
    }


    public function tablaNotificacion(){

        $listado = TipoNotificacion::orderBy('id', 'ASC')->get();

        foreach ($listado as $dato){

            $datosRaw = $this->retornoTituloTipoNotificacion($dato->id);
            $dato->titulo = $datosRaw->titulo;
            $dato->descripcion = $datosRaw->descripcion;
            $dato->descripcionhito = $datosRaw->descripcion_hito;
        }

        return view('backend.admin.recursos.notificacion.tablanotificacion', compact('listado'));
    }


    private function retornoTituloTipoNotificacion($idtipoNoti){

        // solo buscar tipo español

        $infoTexto = NotificacionTextos::where('id_tipo_notificacion', $idtipoNoti)
            ->where('id_idioma_planes', 1)->first();

        return $infoTexto;
    }



    public function indexNotificacionEditar($idTipoNoti){ // tabla notificacion_textos


        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $listado = NotificacionTextos::where('id_tipo_notificacion', $idTipoNoti)->get();

        $contador = 0;
        foreach ($listado as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.recursos.notificacion.editar.vistanotificacioneditar', compact('arrayIdiomas', 'idTipoNoti', 'listado'));
    }


    public function borrarImagenTipoNotificacion(Request $request){

        $rules = array(
            'idtiponoti' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        $infoTipoNoti = TipoNotificacion::where('id', $request->idtiponoti)->first();
        $imagenOld = $infoTipoNoti->imagen;

        if($imagenOld != null){
            if(Storage::disk('archivos')->exists($imagenOld)){
                Storage::disk('archivos')->delete($imagenOld);
            }

            TipoNotificacion::where('id', $infoTipoNoti->id)->update([
                'imagen' => null,
            ]);
        }

        return ['success' => 1];
    }


    public function actualizarImagenTipoNotificacion(Request $request){

        $rules = array(
            'idtiponoti' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($infoTipo = TipoNotificacion::where('id', $request->idtiponoti)->first()){

            $cadena = Str::random(15);
            $tiempo = microtime();
            $union = $cadena . $tiempo;
            $nombre = str_replace(' ', '_', $union);

            $extension = '.' . $request->imagen->getClientOriginalExtension();
            $nombreFoto = $nombre . strtolower($extension);
            $avatar = $request->file('imagen');
            $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

            if($upload){

                $imagenOld = $infoTipo->imagen;

                if($imagenOld != null){
                    if(Storage::disk('archivos')->exists($imagenOld)){
                        Storage::disk('archivos')->delete($imagenOld);
                    }
                }

                TipoNotificacion::where('id', $infoTipo->id)->update([
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


    public function actualizarTextosNotificacion(Request $request){

        $rules = array(
            'idfila' => 'required',
            'titulo' => 'required',
            'descripcion' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        NotificacionTextos::where('id', $request->idfila)->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
        ]);

        return ['success' => 1];
    }



    // ***********************************************************************************

    public function vistaRedesSociales()
    {
        return view('backend.admin.redessociales.vistaredes');
    }

    public function tablaRedesSociales()
    {
        $listado = RedesSociales::orderBy('posicion', 'ASC')->get();

        return view ('backend.admin.redessociales.tablaredes', compact('listado'));
    }

    public function posicionesRedesSociales(Request $request)
    {
        $tasks = RedesSociales::all();

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



    public function nuevaRedSocial(Request $request)
    {
        $rules = array(
            'nombre' => 'required',
            'link' => 'required',
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

                if($info = RedesSociales::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $registro = new RedesSociales();
                $registro->nombre = $request->nombre;
                $registro->link = $request->link;
                $registro->imagen = $nombreFoto;
                $registro->posicion = $nuevaPosicion;
                $registro->save();

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


    public function borrarRedSocial(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($info = RedesSociales::where('id', $request->id)->first()){

            $imagenOld = $info->imagen;

            if(Storage::disk('archivos')->exists($imagenOld)){
                Storage::disk('archivos')->delete($imagenOld);
            }

            RedesSociales::where('id', $request->id)->delete();

            // imagen fue borrada
            return ['success' => 1];
        }else{
            // decir que imagen fue borrada
            return ['success' => 1];
        }
    }







    // ***********************************************************************************

    public function vistaRecursosWeb()
    {
        return view('backend.admin.recursos.recursosweb.vistarecursosweb');
    }

    public function tablaRecursosWeb()
    {
        $listado = RecursosWeb::orderBy('posicion', 'ASC')->get();

        return view ('backend.admin.recursos.recursosweb.tablarecursosweb', compact('listado'));
    }

    public function posicionesRecursosWeb(Request $request)
    {
        $tasks = RecursosWeb::all();

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



    public function nuevaRecursosWeb(Request $request)
    {
        $rules = array(
            'nombre' => 'required',
            'link' => 'required',
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

                if($info = RecursosWeb::orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $registro = new RecursosWeb();
                $registro->nombre = $request->nombre;
                $registro->link = $request->link;
                $registro->imagen = $nombreFoto;
                $registro->posicion = $nuevaPosicion;
                $registro->save();

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


    public function borrarRecursosWeb(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        if($info = RecursosWeb::where('id', $request->id)->first()){

            $imagenOld = $info->imagen;

            if(Storage::disk('archivos')->exists($imagenOld)){
                Storage::disk('archivos')->delete($imagenOld);
            }

            RecursosWeb::where('id', $request->id)->delete();

            // imagen fue borrada
            return ['success' => 1];
        }else{
            // decir que imagen fue borrada
            return ['success' => 1];
        }
    }



    public function informacionRecursoWeb(Request $request)
    {

        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = RecursosWeb::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }


    public function actualizarRecursosWeb(Request $request)
    {

        $rules = array(
            'id' => 'required',
            'nombre' => 'required',
            'link' => 'required'
        );

        // imagen

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($infoRecurso = RecursosWeb::where('id', $request->id)->first()){

            if($request->file('imagen')){

                $cadena = Str::random(15);
                $tiempo = microtime();
                $union = $cadena . $tiempo;
                $nombre = str_replace(' ', '_', $union);

                $extension = '.' . $request->imagen->getClientOriginalExtension();
                $nombreFoto = $nombre . strtolower($extension);
                $avatar = $request->file('imagen');
                $upload = Storage::disk('archivos')->put($nombreFoto, \File::get($avatar));

                if($upload){

                    $imagenOld = $infoRecurso->imagen;

                    if(Storage::disk('archivos')->exists($imagenOld)){
                        Storage::disk('archivos')->delete($imagenOld);
                    }

                    RecursosWeb::where('id', $request->id)->update([
                        'nombre' => $request->nombre,
                        'imagen' => $nombreFoto,
                        'link' => $request->link
                    ]);

                    return ['success' => 1];
                }else{
                    return ['success' => 99];
                }
            }else{
                // no trae imagen

                RecursosWeb::where('id', $request->id)->update([
                    'nombre' => $request->nombre,
                    'link' => $request->link
                ]);

                return ['success' => 1];
            }

        }else{
            // id no encontrado
            return ['success' => 99];
        }
    }




}
