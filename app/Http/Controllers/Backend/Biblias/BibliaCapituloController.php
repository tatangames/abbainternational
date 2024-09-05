<?php

namespace App\Http\Controllers\Backend\Biblias;

use App\Http\Controllers\Controller;
use App\Models\BibliaCapituloBlockTexto;
use App\Models\BibliaCapituloBloque;
use App\Models\BibliaCapitulos;
use App\Models\BibliaCapitulosTextos;
use App\Models\BibliasTextos;
use App\Models\BibliaVersiculo;
use App\Models\BibliaVersiculoBloque;
use App\Models\IdiomaPlanes;
use App\Models\Versiculo;
use App\Models\VersiculoRefran;
use App\Models\VersiculoTextos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BibliaCapituloController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }


    public function vistaLibro($idbiblia){

        return view('backend.admin.biblias.libros.vistalibro', compact('idbiblia'));
    }

    public function tablaLibro($idbiblia){

        $listado = BibliaCapitulos::where('id_biblias', $idbiblia)
            ->orderBy('posicion', 'ASC')
            ->get();

        foreach ($listado as $dato){

            $titulo = $this->retornoTituloCapituloBiblia($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.biblias.libros.tablalibro', compact('listado'));
    }

    private function retornoTituloCapituloBiblia($idcapitulo){

        $titulo = "";
        if($datos = BibliaCapitulosTextos::where('id_biblia_capitulo', $idcapitulo)
            ->where('id_idioma_planes', 1)
            ->first()){
            $titulo = $datos->titulo;
        }

        return $titulo;
    }

    public function actualizarPosicionBibliaLibros(Request $request){

        $tasks = BibliaCapitulos::all();

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


    public function estadoLibro(Request $request)
    {
        $regla = array(
            'idcapitulo' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        BibliaCapitulos::where('id', $request->idcapitulo)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }




    public function vistaBibliaLibro($id)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        return view('backend.admin.biblias.libros.nuevolibro', compact('id', 'arrayIdiomas'));
    }


    public function registrarLibro(Request $request)
    {
        $regla = array(
            'id' => 'required', // idbiblia
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        // array: infoIdIdioma, infoTitulo

            DB::beginTransaction();

            try {

                if($info = BibliaCapitulos::where('id_biblias', $request->id)->orderBy('posicion', 'DESC')->first()){
                    $nuevaPosicion = $info->posicion + 1;
                }else{
                    $nuevaPosicion = 1;
                }

                $registro = new BibliaCapitulos();
                $registro->id_biblias = $request->id;
                $registro->visible = 0;
                $registro->posicion = $nuevaPosicion;
                $registro->save();

                $datosContenedor = json_decode($request->contenedorArray, true);

                // VACIO
                if (empty($datosContenedor)) {
                    return ['success' => 99];
                }

                foreach ($datosContenedor as $filaArray) {

                    $detalle = new BibliaCapitulosTextos();
                    $detalle->id_biblia_capitulo = $registro->id;
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

    }



    public function vistaEditarBibliaLibro($id)
    {
        // id: biblia_capitulos
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $contador = 0;
        $listado = BibliaCapitulosTextos::where('id_biblia_capitulo', $id)->get();
        foreach ($listado as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.biblias.libros.editarlibro', compact('id', 'arrayIdiomas', 'listado'));
    }


    public function actualizarBibliaLibro(Request $request)
    {
        $regla = array(
            'id' => 'required', // id biblia_capitulos
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo,

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoPlanTexto = BibliaCapitulosTextos::where('id', $filaArray['infoIdPlanTexto'])->first()){

                    // actualizar
                    BibliaCapitulosTextos::where('id', $infoPlanTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new BibliaCapitulosTextos();
                    $detalle->id_biblia_capitulo = $request->id;
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



















    // ----------------------PARTE DE CAPITULOS ---------------------------------------------------

    public function vistaCapitulosBloque($idcapitulo)
    {

        // id: biblia_capitulos

        return view('backend.admin.biblias.capitulos.vistacapitulobiblia', compact('idcapitulo'));
    }

    public function tablaCapitulosBloque($idcapitulo)
    {
        $listado = BibliaCapituloBloque::where('id_biblia_capitulo', $idcapitulo)
            ->orderBy('posicion', 'ASC')
            ->get();

        foreach ($listado as $dato){

            $titulo = $this->retornoTituloCapituloBoque($dato->id);
            $dato->titulo = $titulo;
        }

        return view('backend.admin.biblias.capitulos.tablacapitulobiblia', compact('listado'));
    }

    // tabla: biblia_capitulo_block_texto
    private function retornoTituloCapituloBoque($idcapiblock){

        $datos = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idcapiblock)
            ->where('id_idioma_planes', 1)
            ->first();

        return $datos->titulo;
    }


    public function registrarCapituloBloque(Request $request)
    {
        $rules = array(
            'idcapitulo' => 'required',
            'titulo' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            if($info = BibliaCapituloBloque::where('id_biblia_capitulo', $request->idcapitulo)
                ->orderBy('posicion', 'DESC')
                ->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }


            $nuevo = new BibliaCapituloBloque();
            $nuevo->id_biblia_capitulo = $request->idcapitulo;
            $nuevo->visible = 0;
            $nuevo->posicion = $nuevaPosicion;
            $nuevo->save();

            // guardar el idioma por defecto
            $detalle = new BibliaCapituloBlockTexto();
            $detalle->id_biblia_capitulo_block = $nuevo->id;
            $detalle->id_idioma_planes = 1;
            $detalle->titulo = $request->titulo;
            $detalle->save();

            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function informacionCapituloBloque(Request $request)
    {
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = BibliaCapituloBloque::where('id', $request->id)->first()){

            $titulo = $this->retornoTituloCapituloBoque($lista->id);

            return ['success' => 1, 'info' => $lista, 'titulo' => $titulo];
        }else{
            return ['success' => 2];
        }
    }




    public function estadoCapituloBloque(Request $request)
    {
        $regla = array(
            'idbloque' => 'required',
            'estado' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        BibliaCapituloBloque::where('id', $request->idbloque)->update([
            'visible' => $request->estado,
        ]);


        return ['success' => 1];
    }


    public function actualizarPosicionBibliaCapitulosBloque(Request $request){

        $tasks = BibliaCapituloBloque::all();

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



    public function vistaCapitulo($id)
    {
        // biblia_capitulos

        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        return view('backend.admin.biblias.capitulos.nuevocapitulo', compact('id', 'arrayIdiomas'));
    }


    public function registrarCapitulo(Request $request)
    {
        $regla = array(
            'id' => 'required', // id: biblia_capitulos
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        // array: infoIdIdioma, infoTitulo

        DB::beginTransaction();

        try {

            if($info = BibliaCapituloBloque::where('id_biblia_capitulo', $request->id)->orderBy('posicion', 'DESC')->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }

            $registro = new BibliaCapituloBloque();
            $registro->id_biblia_capitulo = $request->id;
            $registro->visible = 0;
            $registro->posicion = $nuevaPosicion;
            $registro->save();

            $datosContenedor = json_decode($request->contenedorArray, true);

            // VACIO
            if (empty($datosContenedor)) {
                return ['success' => 99];
            }

            foreach ($datosContenedor as $filaArray) {

                $detalle = new BibliaCapituloBlockTexto();
                $detalle->id_biblia_capitulo_block = $registro->id;
                $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                $detalle->titulo = $filaArray['infoTitulo'];
                $detalle->textocapitulo = null;
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
    }


    public function vistaEditarCapitulo($id)
    {
        // id: biblia_capitulo_bloque
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $contador = 0;
        $listado = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $id)->get();
        foreach ($listado as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }

        return view('backend.admin.biblias.capitulos.editarcapitulo', compact('id', 'arrayIdiomas', 'listado'));
    }


    public function actualizarCapitulo(Request $request)
    {
        $regla = array(
            'id' => 'required', // id biblia_capitulo_bloque
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo,

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                // comprobar si existe para actualizar o crear segun idioma nuevo
                if($infoPlanTexto = BibliaCapituloBlockTexto::where('id', $filaArray['infoIdPlanTexto'])->first()){

                    // actualizar
                    BibliaCapituloBlockTexto::where('id', $infoPlanTexto->id)->update([
                        'titulo' => $filaArray['infoTitulo'],
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new BibliaCapituloBlockTexto();
                    $detalle->id_biblia_capitulo_block = $request->id;
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


    public function idiomasDisponibleVersiculo(Request $request){

        $regla = array(
            'id' => 'required', // id biblia_capitulo_bloque
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo,

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()){ return ['success' => 0];}


        $listado = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $request->id)->get();

        foreach ($listado as $dato){
            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
            $dato->ididioma = $infoIdioma->id;
        }

        return ['success' => 1, 'listado' => $listado];
    }


    public function buscarTextoVersiculoIdioma(Request $request)
    {
        $regla = array(
            'id' => 'required', // id biblia_capitulo_bloque
            'idioma' => 'required'
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo,

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()){ return ['success' => 0];}

        if($info = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $request->id)
            ->where('id_idioma_planes', $request->idioma)
            ->first()){

            $texto = $info->textocapitulo;

            return ['success' => 1, 'texto' => $texto];
        }else{
            return ['success' => 99];
        }
    }

    public function actualizarVersiculo(Request $request)
    {
        $regla = array(
            'id' => 'required', // id biblia_capitulo_bloque
            'idioma' => 'required',
            'versiculo' => 'required'
        );

        // array: infoIdPlanTexto, infoIdIdioma, infoTitulo,

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()){ return ['success' => 0];}

        if($info = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $request->id)
            ->where('id_idioma_planes', $request->idioma)
            ->first()){

            BibliaCapituloBlockTexto::where('id', $info->id)
                ->update(['textocapitulo' => $request->versiculo]);

            return ['success' => 1];
        }else{
            return ['success' => 99];
        }
    }



// -------------------------------------------------------------------------











    public function busquedaTextoCapitulo(Request $request)
    {
        $regla = array(
            'id' => 'required', // id capitulo
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        if($idbloque = BibliaCapituloBloque::where('id', $request->id)->first()){

            // OBTENER TEXTO DEL CAPITULO
            $info = BibliaCapituloBlockTexto::where('id_biblia_capitulo_block', $idbloque->id)
                    ->where('id_idioma_planes', 1)
                    ->first();

            return ['success' => 1, 'info' => $info];
        }else{
            return ['success' => 99];
        }
    }


    public function guardarTextoVersiculo(Request $request)
    {
        $regla = array(
            'idfila' => 'required',
        );

        // texto

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            BibliaCapituloBlockTexto::where('id', $request->idfila)
                ->update([
                    'textocapitulo' => $request->texto,
                ]);

            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



    public function actualizarPosicionVersiculos(Request $request){

        $tasks = Versiculo::all();

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



}
