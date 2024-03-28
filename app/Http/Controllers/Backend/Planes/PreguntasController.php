<?php

namespace App\Http\Controllers\Backend\Planes;

use App\Http\Controllers\Controller;
use App\Models\BloquePreguntas;
use App\Models\BloquePreguntasTextos;
use App\Models\BloquePreguntasUsuarios;
use App\Models\Departamentos;
use App\Models\IdiomaPlanes;
use App\Models\Iglesias;
use App\Models\ImagenPreguntas;
use App\Models\Pais;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBlockDetaUsuario;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PreguntasController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    // regresa vista de pais
    public function indexPreguntas($idplanbloquedetalle){


        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.vistapreguntas', compact('idplanbloquedetalle'));
    }


    // regresa tabla listado de paises
    public function tablaPreguntas($idplanbloquedetalle){


        $listado = BloquePreguntas::where('id_plan_block_detalle', $idplanbloquedetalle)
        ->orderBy('posicion', 'ASC')
            ->get();

        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.tablapreguntas', compact('listado'));
    }


    // vista a nuevas preguntas
    public function indexNuevasPreguntas($idplanbloquedetalle)
    {
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();
        $arrayImagenes = ImagenPreguntas::orderBy('nombre', 'ASC')->get();




        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.nuevo.vistanuevapregunta', compact('idplanbloquedetalle',
        'arrayIdiomas', 'arrayImagenes'));
    }


    // registrar nueva pregunta
    public function registrarNuevaPregunta(Request $request)
    {

        $regla = array(
            'idplanbloquedetalle' => 'required',
            'toggle' => 'required',
            'idimagen' => 'required',
        );


        // array: infoIdIdioma, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);

            if($info = BloquePreguntas::where('id_plan_block_detalle', $request->idplanbloquedetalle)
                ->orderBy('posicion', 'DESC')
                ->first()){
                $nuevaPosicion = $info->posicion + 1;
            }else{
                $nuevaPosicion = 1;
            }


            $nuevo = new BloquePreguntas();
            $nuevo->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo->id_imagen_pregunta = $request->idimagen;
            $nuevo->visible = 1; // por defecto dejar en visible
            $nuevo->posicion = $nuevaPosicion;
            $nuevo->requerido = $request->toggle;
            $nuevo->save();

            // sus idiomas
            foreach ($datosContenedor as $filaArray) {

                $contenidoHtmlConJavascript = "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $filaArray['infoDescripcion'] . "</body>
                    </html>";


                $detalle = new BloquePreguntasTextos();
                $detalle->id_bloque_preguntas = $nuevo->id;
                $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                $detalle->texto = $contenidoHtmlConJavascript;
                $detalle->save();
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


    public function actualizarPosicionPreguntas(Request $request)
    {
        $tasks = BloquePreguntas::all();

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


    public function indexEditarBloquePregunta($idbloquepregunta)
    {

        $infoPregunta = BloquePreguntas::where('id', $idbloquepregunta)->first();

        $arrayImagenes = ImagenPreguntas::orderBy('nombre', 'ASC')->get();
        $arrayIdiomas = IdiomaPlanes::orderBy('id', 'ASC')->get();

        $arrayBloquePreguntasTextos = BloquePreguntasTextos::where('id_bloque_preguntas', $idbloquepregunta)
            ->orderBy('id_idioma_planes', 'ASC')
            ->get();


        $contador = 0;
        foreach ($arrayBloquePreguntasTextos as $dato){
            $contador++;
            $dato->contador = $contador;

            $infoIdioma = IdiomaPlanes::where('id', $dato->id_idioma_planes)->first();
            $dato->idioma = $infoIdioma->nombre;
        }


        return view('backend.admin.devocional.planes.bloques.bloquedetalle.preguntas.editar.vistaeditarpregunta', compact('infoPregunta',
        'arrayBloquePreguntasTextos', 'arrayImagenes', 'arrayIdiomas'));
    }


    public function editarBloquePreguntas(Request $request)
    {
        $regla = array(
            'idbloquepreguntas' => 'required',
            'idimagen' => 'required',
            'toggle' => 'required',
            'togglevisible' => 'required'
        );


        // array: infoIdBloquePreguntaTextos, infoIdIdioma, infoDescripcion


        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();

        try {

            $datosContenedor = json_decode($request->contenedorArray, true);


            // actualizar
            BloquePreguntas::where('id', $request->idbloquepreguntas)->update([
                'id_imagen_pregunta' => $request->idimagen,
                'requerido' => $request->toggle,
                'visible' => $request->togglevisible
            ]);


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
                if($infoPreguntaTexto = BloquePreguntasTextos::where('id', $filaArray['infoIdBloquePreguntaTextos'])->first()){

                    // actualizar
                    BloquePreguntasTextos::where('id', $infoPreguntaTexto->id)->update([
                        'texto' => $contenidoHtmlConJavascript,
                    ]);

                }else{

                    // como no encontro, se creara

                    $detalle = new BloquePreguntasTextos();
                    $detalle->id_bloque_preguntas = $request->idbloquepreguntas;
                    $detalle->id_idioma_planes = $filaArray['infoIdIdioma'];
                    $detalle->texto = $contenidoHtmlConJavascript;
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





    // ********************************** MEDITACION ***********************************

    public function indexPreguntasMeditacion($idplanbloquedetalle){

        $infoPlanBlockDetaTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idplanbloquedetalle)
            ->where('id_idioma_planes', 1)
            ->first();

        $nombreItem = $infoPlanBlockDetaTexto->titulo;


        return view('backend.admin.meditacion.vistapaismedi', compact('idplanbloquedetalle', 'nombreItem'));
    }


    // regresa tabla listado de paises
    public function tablaPreguntasMeditacion($idplanbloquedetalle){

        $pilaIdBloque = array();

        $listadoBlo = BloquePreguntas::where('id_plan_block_detalle', $idplanbloquedetalle)->get();

        foreach ($listadoBlo as $dato){
            array_push($pilaIdBloque, $dato->id);
        }

        $pilaIdUsuario = array();

        $listadoP = BloquePreguntasUsuarios::whereIn('id_bloque_preguntas', $pilaIdBloque)->get();

        // necesito meter array lista de id usuarios
        foreach ($listadoP as $dato){
            array_push($pilaIdUsuario, $dato->id_usuarios);
        }

        $listado = Usuarios::whereIn('id', $pilaIdUsuario)->get();

        foreach ($listado as $dato){

            $infoIglesia = Iglesias::where('id', $dato->id_iglesia)->first();
            $dato->iglesia = $infoIglesia->nombre;

            $infoDepa = Departamentos::where('id', $infoIglesia->id_departamento)->first();
            $dato->nomdepa = $infoDepa->nombre;

            $infoPais = Pais::where('id', $infoDepa->id_pais)->first();
            $dato->nompais = $infoPais->nombre;

            if($dato->id_genero == 1){
                $dato->genero = "Masculino";
            }else{
                $dato->genero = "Femenino";
            }

            $dato->unido = $dato->nombre . " " . $dato->apellido;
        }

        return view('backend.admin.meditacion.tablapaismedi', compact('listado'));
    }




    public function indexPreguntasMeditacionUsuario($idplanbloquedetalle, $idusuario)
    {

        return view('backend.admin.meditacion.usuario.vistamediusuario', compact('idplanbloquedetalle', 'idusuario'));
    }



    public function tablaPreguntasMeditacionUsuario($idplanbloquedetalle, $idusuario)
    {

        $pilaIdBloque = array();

        $listadoBlo = BloquePreguntas::where('id_plan_block_detalle', $idplanbloquedetalle)->get();

        foreach ($listadoBlo as $dato){
            array_push($pilaIdBloque, $dato->id);
        }

        $listado = BloquePreguntasUsuarios::whereIn('id_bloque_preguntas', $pilaIdBloque)
            ->where('id_usuarios', $idusuario)->get();

        foreach ($listado as $dato){

            // buscar texto de pregunta
            $infoPre = BloquePreguntasTextos::where('id_bloque_preguntas', $dato->id_bloque_preguntas)
                ->where('id_idioma_planes', 1)
                ->first();

            $dato->titulopre = $infoPre->texto;




            $dato->fechaRegistro = date("d-m-Y", strtotime($dato->fecha));

        }


        return view('backend.admin.meditacion.usuario.tablamediusuario', compact( 'listado'));
    }














}
