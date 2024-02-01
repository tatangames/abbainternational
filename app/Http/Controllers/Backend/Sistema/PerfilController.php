<?php

namespace App\Http\Controllers\Backend\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\IdiomaSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class PerfilController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }


    public function indexEditarPerfil(){
        $usuario = auth()->user();

        return view('backend.admin.perfil.vistaperfil', compact('usuario'));
    }

    // editar contraseña del usuario
    public function editarUsuario(Request $request){

        $regla = array(
            'correo' => 'required',
            'password' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){
            return ['success' => 0];
        }

        $usuario = auth()->user();

        Administrador::where('id', $usuario->id)
            ->update([
                'email' => $request->correo,
                'password' => Hash::make($request->password)]);

        return ['success' => 1];
    }




    /// ****************************** IDIOMA SISTEMA ************************************


    // vista a idioma sistema

    public function indexIdiomaSistema(){
        return view('backend.admin.configuracion.idiomasistema.vistaidiomasistema');
    }


    // regresa tabla listado de idioma sistema
    public function tablaIdiomaSistema(){
        $listado = IdiomaSistema::orderBy('id', 'ASC')->get();

        return view('backend.admin.configuracion.idiomasistema.tablaidiomasistema', compact('listado'));
    }

    // agregar nuevo texto
    public function nuevoIdiomaSistema(Request $request){

        $rules = array(
            'txtespanol' => 'required',
            'txtingles' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {

            $nuevo = new IdiomaSistema();
            $nuevo->espanol = $request->txtespanol;
            $nuevo->ingles = $request->txtingles;
            $nuevo->save();

            // registrado correctamente
            DB::commit();
            return ['success' => 1];
        }catch(\Throwable $e){

            DB::rollback();
            return ['success' => 2];
        }
    }


    // informacion con el ID de un idioma sistema
    public function informacionIdiomaSistema(Request $request){
        $rules = array(
            'id' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }


        if($lista = IdiomaSistema::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // actualizar con el ID de un departamento
    public function actualizarIdiomaSistema(Request $request){
        $rules = array(
            'id' => 'required',
            'txtespanol' => 'required',
            'txtingles' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);
        if ( $validator->fails()){
            return ['success' => 0];
        }

        if(IdiomaSistema::where('id', $request->id)->first()){

            IdiomaSistema::where('id', $request->id)->update([
                'espanol' => $request->txtespanol,
                'ingles' => $request->txtingles
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }



}
