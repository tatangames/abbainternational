@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />

@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }
</style>

<div id="divcontenedor" style="display: none">

    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">

                <p style="font-weight: bold; font-size: 16px">País: {{$nombrePais}}</p>

            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Usuarios</li>
                    <li class="breadcrumb-item active">Todos por País</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Usuarios</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="tablaDatatable">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- MODAL PARA VER INFORMACION DEL USUARIO -->

    <div class="modal fade" id="modalUsuario">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Usuario</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-u">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text" disabled class="form-control" id="nombre-u" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Género</label>
                                        <input type="text" disabled class="form-control" id="genero-u" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Fecha Nacimiento</label>
                                        <input type="text" disabled class="form-control" id="fechanacimiento-u" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Fecha Registro en la App</label>
                                        <input type="text" disabled class="form-control" id="fecharegistro-u" autocomplete="off">
                                    </div>


                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>




</div>


@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var idpais = {{ $idpais }};
            var ruta = "{{ URL::to('/admin/usuarios/pais/todos/tabla') }}/" + idpais;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>


        function informacionUsuario(idusuario){

            openLoading();

            document.getElementById("formulario-u").reset();

            axios.post('/admin/usuarios/pais/info/usuario',{
                'id': idusuario
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalUsuario').modal('show');

                        $('#nombre-u').val(response.data.info.nombre);
                        $('#genero-u').val(response.data.nombreGe);
                        $('#fechanacimiento-u').val(response.data.fechaNac);
                        $('#fecharegistro-u').val(response.data.fechaRe);

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });

        }






    </script>


@endsection
