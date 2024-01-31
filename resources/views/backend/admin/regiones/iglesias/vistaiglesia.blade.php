@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />

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
                <p style="font-weight: bold; font-size: 16px">Departamento: {{$nombreDepa}}</p>

                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nueva Iglesia
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Regiones</li>
                    <li class="breadcrumb-item active">Iglesia</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Iglesias</h3>
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

    <div class="modal fade" id="modalAgregar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Iglesias</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-nuevo">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">


                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text" maxlength="50" class="form-control" id="nombre-nuevo" autocomplete="off">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="nuevo()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal editar -->
    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Iglesia</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="formulario-editar">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="hidden" id="id-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="text" maxlength="50" class="form-control" id="nombre-editar" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Visibilidad</label><br>
                                        <label class="switch" style="margin-top:10px">
                                            <input type="checkbox" id="toggle-visible">
                                            <div class="slider round">
                                                <span class="on">Activo</span>
                                                <span class="off">Inactivo</span>
                                            </div>
                                        </label>
                                    </div>



                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editar()">Guardar</button>
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
            var iddepa = {{ $iddepa }};
            var ruta = "{{ URL::to('/admin/region/iglesias/tabla') }}/" + iddepa;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            var iddepa = {{ $iddepa }};
            var ruta = "{{ URL::to('/admin/region/iglesias/tabla') }}/" + iddepa;
            $('#tablaDatatable').load(ruta);
        }

        // abre modal para agregar nuevo iglesia
        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        // envia datos de nueva iglesia al servidor
        function nuevo(){
            var nombre = document.getElementById('nombre-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerida');
                return;
            }

            // maximo de caracteres para zona horaria
            if(nombre.length > 50){
                toastr.error('Nombre máximo 50 caracteres');
                return;
            }

            var iddepa = {{ $iddepa }};

            openLoading();
            let formData = new FormData();
            formData.append('iddepa', iddepa);
            formData.append('nombre', nombre);

            axios.post('/admin/region/iglesias/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Registrado correctamente');
                        $('#modalAgregar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
                    closeLoading();
                });
        }

        // informacion de una iglesia
        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/region/iglesias/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);
                        $('#nombre-editar').val(response.data.info.nombre);

                        if(response.data.info.visible === 0){
                            $("#toggle-visible").prop("checked", false);
                        }else{
                            $("#toggle-visible").prop("checked", true);
                        }

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        // editar datos de una iglesia
        function editar(){
            let id = document.getElementById('id-editar').value;
            let nombre = document.getElementById('nombre-editar').value;
            let t = document.getElementById('toggle-visible').checked;
            let visible = t ? 1 : 0;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 50){
                toastr.error('Nombre máximo 50 caracteres');
                return;
            }

            openLoading();
            let formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('visible', visible);

            axios.post('/admin/region/iglesias/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al actualizar');
                    closeLoading();
                });
        }




    </script>


@endsection
