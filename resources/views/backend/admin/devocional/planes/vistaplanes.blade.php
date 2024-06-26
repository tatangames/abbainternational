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
    #card-header-color {
        background-color: #3c8cbb !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <button type="button" onclick="nuevoPlan()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo Devocional
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Devocional</li>
                    <li class="breadcrumb-item active">Nuevo Devocional</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Devocionales</h3>
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

<!-- modal agregar -->
<div class="modal fade" id="modalAgregar">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Imagen</h4>
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
                                    <label>Descripción</label>
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="descripcion-nuevo" placeholder="Descripción">
                                </div>


                                <div class="form-group">
                                    <div>
                                        <label>Imagen</label>
                                        <p>Tamaño no superar: 1000 x 1000 px</p>
                                    </div>
                                    <br>
                                    <div class="col-md-10">
                                        <input type="file" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="nuevo()">Guardar</button>
            </div>
        </div>
    </div>
</div>


<!-- modal editar-->
<div class="modal fade" id="modalEditar" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Imagen</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-editar">
                    <div class="card-body">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label>Descripción</label>
                                <input type="hidden" id="id-editar">
                                <input type="text" maxlength="100" autocomplete="off" class="form-control" id="descripcion-editar">
                            </div>

                            <div class="form-group">
                                <div>
                                    <label>Imagen</label>
                                    <p>Tamaño no superar: 1000 x 1000 px</p>
                                </div>
                                <br>
                                <div class="col-md-10">
                                    <input type="file" style="color:#191818" id="imagen-editar" accept="image/jpeg, image/jpg, image/png"/>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="editar()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery-ui-drag.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-drag.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var ruta = "{{ URL::to('/admin/planes/tabla') }}";
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ URL::to('/admin/planes/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // vista para agregar nuevo plan
        function nuevoPlan(){
            window.location.href="{{ url('/admin/planes/agregar/nuevo/index') }}";
        }

        // vista para editar el plan

        function vistaEditarPlan(idplan){
            window.location.href="{{ url('/admin/planes/vista/editar/index') }}/" + idplan;
        }

        // vista para ver los bloques
        function vistaListadoBloques(idplan){
            window.location.href="{{ url('/admin/planesbloques/vista/index') }}/" + idplan;
        }


        function preguntaActivar(idplan){

            Swal.fire({
                title: '¿Activar?',
                text: "",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    configurarPlan(1, idplan);
                }
            })
        }


        function preguntaDeshabilitar(idplan){

            Swal.fire({
                title: '¿Deshabilitar?',
                text: "",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    configurarPlan(0, idplan);
                }
            })

        }


        function configurarPlan(estado, idplan){

            let formData = new FormData();
            formData.append('idplan', idplan);
            formData.append('estado', estado);
            openLoading();

            axios.post('/admin/planes/activacion', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        toastr.success('Actualizado');
                        recargar();
                    }
                    else if(response.data.success === 2){

                        Swal.fire({
                            title: 'No Activado',
                            text: "Se requiere crear las fechas a este Plan Devocional",
                            icon: 'info',
                            showCancelButton: false,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Si'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }
                    else{
                        toastr.error('Error al actualizar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al actualizar');
                    closeLoading();
                });
        }


        function modalBorrar(id){

            Swal.fire({
                title: 'Borrar Devocional',
                text: "Esto borrara todo registro",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    borrarRegistro(id)
                }
            })
        }


        function borrarRegistro(id){

            let formData = new FormData();
            formData.append('idplan', id);
            openLoading();

            axios.post('/admin/planes/borradototal', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        toastr.success('Borrado');
                        recargar();
                    }
                    else{
                        toastr.error('Error al borrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al borrar');
                    closeLoading();
                });
        }


    </script>


@endsection
