<?php

namespace App\Http\Controllers\Api\Planes;

use App\Http\Controllers\Controller;
use App\Models\BloqueCuestionario;
use App\Models\BloqueCuestionarioTextos;
use App\Models\BloquePreguntas;
use App\Models\Iglesias;
use App\Models\Planes;
use App\Models\PlanesBlockDetalle;
use App\Models\PlanesBlockDetaTextos;
use App\Models\PlanesBlockDetaUsuario;
use App\Models\PlanesBloques;
use App\Models\PlanesContenedor;
use App\Models\PlanesContenedorTextos;
use App\Models\PlanesTextos;
use App\Models\PlanesUsuarios;
use App\Models\PlanesUsuariosContinuar;
use App\Models\ZonaHoraria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use DateTime;

class ApiPlanesController extends Controller
{


    public function buscarPlanesNoAgregados(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {


            $arrayContenedor = PlanesContenedor::where('visible', 1)->get();

            // todos los planes de mi usuario
            $arrayId = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            // meter todos los planes, menos elegidos por el usuario ya
            foreach ($arrayContenedor as $dato){

                $hayInfo = 0;

                // obtener todos los planes NO elegido por el usuario y sean visible
                $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                    ->where('id_planes_contenedor', $dato->id)
                    ->where('visible', 1)
                    ->get();

                if ($arrayPlanes->isNotEmpty()) {
                    $hayInfo = 1;
                }

                $dato->hayinfo = $hayInfo;
            }


            // EVITAR LOS TITULOS VACIOS
            $arrayPlanesValidos = $this->retornoPlanesNoElegidos($arrayContenedor, $userToken->id, $idiomaTextos);

            return [
                'success' => 1,
                'listado' => $arrayPlanesValidos,
                ];
        }else{
            return ['success' => 99];
        }
    }

    // COMO IDIOMATEXTOS DEVUELVE 0 POR DEFECTO, Y EL ID 1 ES MINIMO EN LA BASE DE DATOS
    private function reseteoIdiomaTextos($idiomatextos)
    {
        if($idiomatextos == 0){
            $idiomatextos = 1;
        }

        return $idiomatextos;
    }

    // RETORNA POR TITULO Y SUS PLANES DISPONIBLES POR USUARIO
    private function retornoPlanesNoElegidos($arraybuscar, $idusuario, $idiomaTextos){

        // todos los planes de mi usuario
        $arrayId = PlanesUsuarios::where('id_usuario', $idusuario)
            ->select('id_planes')
            ->get();

        $pilaIdContenedor = array();

        foreach ($arraybuscar as $item){
            if($item->hayinfo > 0){
                array_push($pilaIdContenedor, $item->id);
            }
        }

        $planesContenedor = PlanesContenedor::whereIn('id', $pilaIdContenedor)
            ->orderBy('posicion', 'ASC')
            ->get();

        $resultsBloque = array();
        $index = 0;

        foreach ($planesContenedor as $dato){
            array_push($resultsBloque, $dato);


            // obtener el titulo segun idioma
            $tituloContenedor = $this->retornoTituloContenedorPlan($idiomaTextos, $dato->id);
            $dato->titulo = $tituloContenedor;


            // obtener todos los planes NO elegido por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                ->where('id_planes_contenedor', $dato->id)
                ->get();

            foreach ($arrayPlanes as $item){
                $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $item->id);
                $item->titulo = $arrayRaw['titulo'];
                $item->subtitulo = $arrayRaw['subtitulo'];
            }

            $resultsBloque[$index]->detalle = $arrayPlanes;
            $index++;
        }

        return $planesContenedor;
    }


    // RETORNO DE TITULO DEL CONTENEDOR DEL PLAN SEGUN IDIOMA
    private function retornoTituloContenedorPlan($idiomaTextos, $idContenedor){

        // si encuentra idioma solicitado
        if($infoPlanContenedorTexto = PlanesContenedorTextos::where('id_planes_contenedor', $idContenedor)
            ->where('id_idioma_planes', $idiomaTextos)
            ->first()){

            return $infoPlanContenedorTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoPlanContenedorTexto = PlanesContenedorTextos::where('id_planes_contenedor', $idContenedor)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoPlanContenedorTexto->titulo;
        }
    }


    // RETORNO DE NOMBRE, SUBTITULO DEL PLAN SEGUN IDIOMA
    private function retornoTituloPlan($idiomaplan, $idplan){

            // si encuentra idioma solicitado
        if($infoPlanTexto = PlanesTextos::where('id_planes', $idplan)
            ->where('id_idioma_planes', $idiomaplan)
            ->first()){

            return ['titulo' => $infoPlanTexto->titulo,
                'subtitulo' => $infoPlanTexto->subtitulo
            ];

        }else{
            // si no encuentra sera por defecto español

            $infoPlanTexto = PlanesTextos::where('id_planes', $idplan)
                ->where('id_idioma_planes', 1)
                ->first();

            return ['titulo' => $infoPlanTexto->titulo,
                    'subtitulo' => $infoPlanTexto->subtitulo
                ];
        }
    }



    // ver informacion de un plan para poder seleccionarlo
    public function informacionPlanSeleccionado(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'idplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        if($infoPlan = Planes::where('id', $request->idplan)->first()){

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            $titulo = "";
            $subtitulo = null;
            $descripcion = null;

            if($infoPlanTextos = PlanesTextos::where('id_planes', $request->idplan)
                ->where('id_idioma_planes', $idiomaTextos)
                ->first()){
                $titulo = $infoPlanTextos->titulo;
                $subtitulo = $infoPlanTextos->subtitulo;
                $descripcion = $infoPlanTextos->descripcion;
            }

            return ['success' => 1,
                'imagen' => $infoPlan->imagenportada,
                'titulo' => $titulo,
                'subtitulo' => $subtitulo,
                'descripcion' => $descripcion
                ];
        }else{
            return ['success' => 99];
        }
    }


    // devuelve lista de planes que no he seleccionado aun, por id contenedor
    public function listadoPlanesContenedor(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idcontenedor' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        // idioma, segun el usuario
        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            // todos los planes de mi usuario
            $arrayId = PlanesUsuarios::where('id_usuario', $userToken->id)
                ->select('id_planes')
                ->get();

            $hayInfo = 0;

            // todos los planes del contenedor, menos seleccionados por el usuario
            $arrayPlanes = Planes::whereNotIn('id', $arrayId)
                ->where('id_planes_contenedor', $request->idcontenedor)
                ->get();

            if ($arrayPlanes->isNotEmpty()) {
                $hayInfo = 1;

                foreach ($arrayPlanes as $item){
                    $arrayRaw = $this->retornoTituloPlan($idiomaTextos, $item->id);
                    $item->titulo = $arrayRaw['titulo'];
                    $item->subtitulo = $arrayRaw['subtitulo'];
                }
            }

            return [
                'success' => 1,
                'listaplanes' => $arrayPlanes,
                'hayinfo' => $hayInfo
            ];
        }else{
            return ['success' => 99];
        }
    }


    // selecciona un plan para iniciarlo
    public function iniciarPlanNuevo(Request $request){
        $rules = array(
            'idplan' => 'required',
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            if(PlanesUsuarios::where('id_usuario', $userToken->id)
                ->where('id_planes', $request->idplan)->first()){

                // ya estaba agregado
                return ['success' => 1,
                    'msg' => 'plan ya estaba registrado'];
            }else{
                DB::beginTransaction();

                try {

                    $nuevoPlan = new PlanesUsuarios();
                    $nuevoPlan->id_usuario = $userToken->id;
                    $nuevoPlan->id_planes = $request->idplan;
                    $nuevoPlan->fecha = Carbon::now('America/El_Salvador');
                    $nuevoPlan->save();

                    DB::commit();
                    return ['success' => 2];

                }catch(\Throwable $e){
                    DB::rollback();
                    return ['success' => 99];
                }
            }
        }else{
            return ['success' => 99];
        }
    }


    // devuelve mis planes que he seleccionado, habra algunos que pasaran a 'completados' pero
    // se verificaran dinamicamente
    public function listadoMisPlanes(Request $request)
    {
        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idPlanContinuar = 0;
            $arrayContinuar = null;
            $haycontinuar = 0;
            if($infoContinuar = PlanesUsuariosContinuar::where('id_usuarios', $userToken->id)->first()){
                $haycontinuar = 1;

                $idPlanContinuar = $infoContinuar->id_planes;

                // plan ultimo para continuar
                $arrayContinuar = PlanesUsuariosContinuar::where('id_usuarios', $userToken->id)
                    ->take(1) // por seguridad tomar solo 1
                    ->get();

                foreach ($arrayContinuar as $dato){
                    $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                    $dato->titulo = $titulosRaw['titulo'];
                    $dato->subtitulo = $titulosRaw['subtitulo'];

                    $infoP = Planes::where('id', $dato->id_planes)->first();
                    $dato->imagen = $infoP->imagen;
                    $dato->imagenportada = $infoP->imagenportada;
                    $dato->barra_progreso = $infoP->barra_progreso;
                    $dato->idplan = $infoP->id;
                }
            }

            // obtener todos los planes menos el de Continuar
           $arrayPlanesUser = PlanesUsuarios::where('id_usuario', $userToken->id)
               ->whereNotIn('id_planes', [$idPlanContinuar])
               ->get();

            foreach ($arrayPlanesUser as $dato){

                $titulosRaw = $this->retornoTituloPlan($idiomaTextos, $dato->id_planes);

                $dato->titulo = $titulosRaw['titulo'];
                $dato->subtitulo = $titulosRaw['subtitulo'];

                $infoP = Planes::where('id', $dato->id_planes)->first();
                $dato->imagen = $infoP->imagen;
                $dato->imagenportada = $infoP->imagenportada;
                $dato->barra_progreso = $infoP->barra_progreso;
                $dato->idplan = $infoP->id;
            }

            $datosOrdenados = $arrayPlanesUser->sortBy('titulo')->values();

            $hayinfo = 0;
            if ($arrayContinuar != null && $arrayContinuar->isNotEmpty() ) {
                $hayinfo = 1;
            }

            if ($arrayPlanesUser != null && $datosOrdenados->isNotEmpty()) {
                $hayinfo = 1;
            }


            return ['success' => 1,
                'haycontinuar' => $haycontinuar,
                'hayinfo' => $hayinfo,
                'listacontinuar' => $arrayContinuar,
                'listaplanes' => $datosOrdenados,
            ];

        }else{
            return ['success' => 99];
        }
    }

    // devuelve informacion del plan a continuar, todos el bloque
    public function informacionBloqueMiPlan(Request $request){

        $rules = array(
            'idiomaplan' => 'required',
            'iduser' => 'required',
            'idplan' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

        if ($userToken = JWTAuth::user($tokenApi)) {

            $infoPlan = Planes::where('id', $request->idplan)->first();

            // obtener zona horaria
            $infoIglesia = Iglesias::where('id', $userToken->id_iglesia)->first();
            $zonaHoraria = ZonaHoraria::where('id', $infoIglesia->id_zona_horaria)->first();


            // obtener todos los bloques ordenados por fecha
            $arrayBloques = PlanesBloques::where('id_planes', $request->idplan)
                ->where('visible', 1)
                ->orderBy('fecha_inicio', 'ASC')
                ->get();

            $contador = 0;
             $resultsBloque = array();
             $index = 0;


            // con esto se conoce si hay un dia con informacion para Hoy, sino se tomara el ultimo
            // en la aplicacion
            $hayDiaActual = 0;

            foreach ($arrayBloques as $dato){
                array_push($resultsBloque, $dato);

                $contador++;
                $dato->abreviatura = $this->retorno3LetrasFechasIdioma($idiomaTextos, $dato->fecha_inicio);
                $dato->contador = $contador;

                // fecha inicio del bloque
                $fecha1 = Carbon::parse($dato->fecha_inicio);

                // fecha-horario actual segun usuario zona horaria
                $fecha2 = Carbon::parse(now(), $zonaHoraria->zona);

                if($fecha1->isSameDay($fecha2)){
                    $dato->mismodia = 1;
                    $hayDiaActual = 1;
                }else{
                    $dato->mismodia = 0;
                }

                // para mostrar o no el bloque, OSEA ESPERAR FECHA PARA QUE APAREZCA
                // SETEAR ESPERAR_FECHA PARA MOSTRAR BLOQUE
                if($dato->esperar_fecha == 1){

                    //gte: mayor o igual
                    if($fecha2->gte($fecha1)){
                        // SET
                        $dato->esperar_fecha = 0;
                    }
                }


                // agregar detalle bloques
                $arrayDetaBloque = PlanesBlockDetalle::where('id_planes_bloques', $dato->id)
                    ->orderBy('posicion', 'ASC')
                    ->get();


                foreach ($arrayDetaBloque as $datoArr){
                    $datoArr->titulo = $this->retornoTituloBloquesTextos($idiomaTextos, $datoArr->id);

                    // saber si esta check para mi usuario
                    if($infoDeta = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                    ->where('id_planes_block_deta', $datoArr->id)
                    ->first()){
                        if($infoDeta->completado == 1){
                            $datoArr->completado = 1;
                        }else{
                            $datoArr->completado = 0;
                        }
                    }else{
                        $datoArr->completado = 0;
                    }

                    // verificar si tiene preguntas, o alguna activa para mostrarlas
                    $hayPreguntas = 0;
                    $arrayPreguntas = BloquePreguntas::where('id_plan_block_detalle', $datoArr->id)
                        ->where('visible', 1)
                        ->count();

                    if($arrayPreguntas > 0){
                        $hayPreguntas = 1;
                    }

                    $datoArr->tiene_preguntas = $hayPreguntas;
                }

                $resultsBloque[$index]->detalle = $arrayDetaBloque;
                $index++;
            }


            // Para comparar en la aplicacion que sino hay info para este dia, este id sera el bloque que se
            // le cambiara el estilo
            $idUltimoBloque = 0;


            if ($arrayBloques->isNotEmpty() && $hayDiaActual == 0) {

                $encontroBloque = true;

                // encontrar cual es el siguiente bloque que deberia cargarse
                foreach ($arrayBloques as $bloque){
                    $fecha1 = Carbon::parse($bloque->fecha_inicio);
                    $fecha2 = Carbon::parse(now(), $zonaHoraria->zona);

                    if($fecha1->gte($fecha2)){
                        $idUltimoBloque = $bloque->id;
                        $encontroBloque = false;
                        break;
                    }
                }

                if($encontroBloque){
                    $ultimoElemento = $arrayBloques->last();
                    $idUltimoBloque = $ultimoElemento->id;
                }
            }


            return ['success' => 1,
                'portada' => $infoPlan->imagenportada,
                'haydiaactual' => $hayDiaActual,
                'idultimobloque' => $idUltimoBloque,
                'listado' => $arrayBloques,


                ];
        }else{
            return ['success' => 99];
        }
    }


    // RETORNA TITULO DEL BLOQUE DETALLE TEXTOS
    private function retornoTituloBloquesTextos($idiomaTextos, $idBlockDetalle){

        if($infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDetalle)
            ->where('id_idioma_planes', $idiomaTextos)
            ->first()){

            return $infoTituloTexto->titulo;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = PlanesBlockDetaTextos::where('id_planes_block_detalle', $idBlockDetalle)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoTituloTexto->titulo;
        }
    }



    // RETORNA 3 PRIMERAS LETRAS PARA LOS BLOQUES DE LOS PLANES
    private function retorno3LetrasFechasIdioma($idiomaTextos, $fecha){

        $dateTime = new DateTime($fecha);

        if($idiomaTextos == 1){ // espanol

            $intlDateFormatter = new \IntlDateFormatter('es_ES', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }
        else if($idiomaTextos == 2){ // ingles
            $intlDateFormatter = new \IntlDateFormatter('en_US', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }else{
            // defecto: espanol
            $intlDateFormatter = new \IntlDateFormatter('es_ES', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMM');
            $intlDateFormatter->setPattern('MMM d');
        }

        return $intlDateFormatter->format($dateTime);
    }


    // actualizar el check de cada plan
    public function actualizarCheckBloqueMiPlan(Request $request)
    {

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
            'valor' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0, 'msj' => "validación incorrecta"];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            // si existe, solo actualizar
            if($idinfo = PlanesBlockDetaUsuario::where('id_usuario', $userToken->id)
                ->where('id_planes_block_deta', $request->idblockdeta)
                ->first()){

                PlanesBlockDetaUsuario::where('id', $idinfo->id)
                    ->update([
                        'completado' => $request->valor,
                    ]);

                return ['success' => 1];
            }else{

                // se debera crear
                DB::beginTransaction();

                try {

                    $detalle = new PlanesBlockDetaUsuario();
                    $detalle->id_usuario = $userToken->id;
                    $detalle->id_planes_block_deta = $request->idblockdeta;
                    $detalle->completado = 1;
                    $detalle->save();

                    DB::commit();
                    return ['success' => 1];

                }catch(\Throwable $e){
                    DB::rollback();
                    return ['success' => 99];
                }
            }
        }else{
            return ['success' => 99];
        }
    }


    public function informacionCuestionarioBloque(Request $request){

        $rules = array(
            'iduser' => 'required',
            'idblockdeta' => 'required',
            'idioma' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0,
                'msj' => "validación incorrecta"
            ];
        }

        $tokenApi = $request->header('Authorization');

        if ($userToken = JWTAuth::user($tokenApi)) {

            $idiomaTextos = $this->reseteoIdiomaTextos($request->idiomaplan);

            // comprueba que al menos haya un cuestionario disponible
            if($info = BloqueCuestionarioTextos::where('id_bloque_detalle', $request->idblockdeta)
                ->first()){

                $titulo = $this->retornoTituloCuestionarioIdioma($info->id, $idiomaTextos);

                return ['success' => 1,
                       'titulo' => $titulo,
                ];
            }else{

                return ['success' => 2,
                    'msg' => "No hay cuestionario"
                    ];
            }


        }else{
            return ['success' => 99];
        }
    }


    private function retornoTituloCuestionarioIdioma($idBlockDeta, $idiomaTexto){

        if($infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
            ->where('id_idioma_planes', $idiomaTexto)
            ->first()){

            return $infoTituloTexto->texto;

        }else{
            // si no encuentra sera por defecto español

            $infoTituloTexto = BloqueCuestionarioTextos::where('id_bloque_detalle', $idBlockDeta)
                ->where('id_idioma_planes', 1)
                ->first();

            return $infoTituloTexto->texto;
        }

    }


}
