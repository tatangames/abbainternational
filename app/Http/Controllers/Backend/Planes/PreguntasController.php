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


    // REGISTRAR PREGUNTAS POR DEFECTO
    // 18/05/2024

    public function registrarNuevaPreguntaDefecto(Request $request)
    {
        $regla = array(
            'idplanbloquedetalle' => 'required'
        );


        // array: infoIdIdioma, infoDescripcion

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();

        try {

            // Evitar que se agreguen 2 veces las preguntas por defecto
            if(BloquePreguntas::where('id_plan_block_detalle', $request->idplanbloquedetalle)->first()){
                return ['success' => 1];
            }


            $pregunta1 = "Registra el versículo que Dios está usando para hablarte hoy";
            $pregunta2 = "1. Padre, ¿Qué me enseña esta escritura acerca de tí?";
            $pregunta3 = "2. Padre, ¿Qué me enseña esta escritura acerca de la vida cristiana?";
            $pregunta4 = "3. Padre, ¿Qué debo hacer con tu palabra revelada hoy?";
            $pregunta5 = "4. Padre, ¿cómo debo orar?";


            $pregunta1Ingles = "What God is using to speak to you today";
            $pregunta2Ingles = "1. Father, what does this scripture teach me about you?";
            $pregunta3Ingles = "2. Father, what does this scripture teach me about the Christian life?";
            $pregunta4Ingles = "3. Father, what should I do with your revealed word today?";
            $pregunta5Ingles = "4. Father, how should I pray?";


            // ********** PREGUNTA 1 **************



            $nuevo1 = new BloquePreguntas();
            $nuevo1->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo1->id_imagen_pregunta = 1;
            $nuevo1->visible = 1;
            $nuevo1->posicion = 1;
            $nuevo1->requerido = 1;
            $nuevo1->save();


            $contenido1= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta1 . "</body>
                    </html>";

            $contenido1Ingles= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta1Ingles . "</body>
                    </html>";


            $detalleE1 = new BloquePreguntasTextos();
            $detalleE1->id_bloque_preguntas = $nuevo1->id;
            $detalleE1->id_idioma_planes = 1;
            $detalleE1->texto = $contenido1;
            $detalleE1->save();

            $detalleI1 = new BloquePreguntasTextos();
            $detalleI1->id_bloque_preguntas = $nuevo1->id;
            $detalleI1->id_idioma_planes = 2;
            $detalleI1->texto = $contenido1Ingles;
            $detalleI1->save();



            // ********** PREGUNTA 2 **************

            $nuevo2 = new BloquePreguntas();
            $nuevo2->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo2->id_imagen_pregunta = 2;
            $nuevo2->visible = 1;
            $nuevo2->posicion = 2;
            $nuevo2->requerido = 1;
            $nuevo2->save();


            $contenido2= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta2 . "</body>
                    </html>";

            $contenido2Ingles= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta2Ingles . "</body>
                    </html>";


            $detalleE2 = new BloquePreguntasTextos();
            $detalleE2->id_bloque_preguntas = $nuevo2->id;
            $detalleE2->id_idioma_planes = 1;
            $detalleE2->texto = $contenido2;
            $detalleE2->save();

            $detalleI2 = new BloquePreguntasTextos();
            $detalleI2->id_bloque_preguntas = $nuevo2->id;
            $detalleI2->id_idioma_planes = 2;
            $detalleI2->texto = $contenido2Ingles;
            $detalleI2->save();




            // ********** PREGUNTA 3 **************

            $nuevo3 = new BloquePreguntas();
            $nuevo3->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo3->id_imagen_pregunta = 3;
            $nuevo3->visible = 1;
            $nuevo3->posicion = 3;
            $nuevo3->requerido = 1;
            $nuevo3->save();


            $contenido3= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta3 . "</body>
                    </html>";


            $contenido3Ingles= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta3Ingles . "</body>
                    </html>";



            $detalleE3 = new BloquePreguntasTextos();
            $detalleE3->id_bloque_preguntas = $nuevo3->id;
            $detalleE3->id_idioma_planes = 1;
            $detalleE3->texto = $contenido3;
            $detalleE3->save();

            $detalleI3 = new BloquePreguntasTextos();
            $detalleI3->id_bloque_preguntas = $nuevo3->id;
            $detalleI3->id_idioma_planes = 2;
            $detalleI3->texto = $contenido3Ingles;
            $detalleI3->save();



            // ********** PREGUNTA 4 **************

            $nuevo4 = new BloquePreguntas();
            $nuevo4->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo4->id_imagen_pregunta = 4;
            $nuevo4->visible = 1;
            $nuevo4->posicion = 4;
            $nuevo4->requerido = 1;
            $nuevo4->save();


            $contenido4= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta4 . "</body>
                    </html>";

            $contenido4Ingles= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta4Ingles . "</body>
                    </html>";


            $detalleE4 = new BloquePreguntasTextos();
            $detalleE4->id_bloque_preguntas = $nuevo4->id;
            $detalleE4->id_idioma_planes = 1;
            $detalleE4->texto = $contenido4;
            $detalleE4->save();

            $detalleI4 = new BloquePreguntasTextos();
            $detalleI4->id_bloque_preguntas = $nuevo4->id;
            $detalleI4->id_idioma_planes = 2;
            $detalleI4->texto = $contenido4Ingles;
            $detalleI4->save();




            // ********** PREGUNTA 5 **************

            $nuevo5 = new BloquePreguntas();
            $nuevo5->id_plan_block_detalle = $request->idplanbloquedetalle;
            $nuevo5->id_imagen_pregunta = 5;
            $nuevo5->visible = 1;
            $nuevo5->posicion = 5;
            $nuevo5->requerido = 1;
            $nuevo5->save();


            $contenido5= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta5 . "</body>
                    </html>";

            $contenido5Ingles= "<html>
                    <head>
                    <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    </head>
                    <body>" . $pregunta5Ingles . "</body>
                    </html>";


            $detalleE5 = new BloquePreguntasTextos();
            $detalleE5->id_bloque_preguntas = $nuevo5->id;
            $detalleE5->id_idioma_planes = 1;
            $detalleE5->texto = $contenido5;
            $detalleE5->save();

            $detalleI5 = new BloquePreguntasTextos();
            $detalleI5->id_bloque_preguntas = $nuevo5->id;
            $detalleI5->id_idioma_planes = 2;
            $detalleI5->texto = $contenido5Ingles;
            $detalleI5->save();


            // completado y actualizado
            DB::commit();
            return ['success' => 2];
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



        $listado =  DB::table('bloque_preguntas_usuarios AS bpu')
            ->join('bloque_preguntas AS b', 'bpu.id_bloque_preguntas', '=', 'b.id')
            ->select('bpu.id_bloque_preguntas', 'bpu.id_usuarios', 'bpu.texto', 'bpu.fecha', 'b.posicion')
            ->whereIn('bpu.id_bloque_preguntas', $pilaIdBloque)
            ->where('bpu.id_usuarios', $idusuario)
            ->orderBy('b.posicion', 'ASC')
            ->get();



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
