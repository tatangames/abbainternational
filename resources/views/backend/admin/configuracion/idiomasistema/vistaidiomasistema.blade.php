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
                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo Texto
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Regiones</li>
                    <li class="breadcrumb-item active">Idioma Sistema</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Textos</h3>
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
                    <h4 class="modal-title">Nuevo Texto</h4>
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
                                        <label>Texto Español</label>
                                        <input type="text" maxlength="300" class="form-control" id="espanol-nuevo" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Texto Ingles</label>
                                        <input type="text" maxlength="300" class="form-control" id="ingles-nuevo" autocomplete="off">
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
                    <h4 class="modal-title">Editar Texto</h4>
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
                                        <label>Texto Español</label>
                                        <input type="text" maxlength="300" class="form-control" id="espanol-editar" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label>Texto Ingles</label>
                                        <input type="text" maxlength="300" class="form-control" id="ingles-editar" autocomplete="off">
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
            var ruta = "{{ URL::to('/admin/idiomasistema/tabla') }}";
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            var ruta = "{{ url('/admin/idiomasistema/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // abre modal para agregar nuevo pais
        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        // envia datos de nuevo pais al servidor
        function nuevo(){
            var txtespanol = document.getElementById('espanol-nuevo').value;
            var txtingles = document.getElementById('ingles-nuevo').value;

            if(txtespanol === ''){
                toastr.error('Texto español es requerido');
                return;
            }

            if(txtingles === ''){
                toastr.error('Texto ingles es requerido');
                return;
            }

            // maximo de caracteres texto espanol
            if(txtespanol.length > 300){
                toastr.error('Texto español máximo 300 caracteres');
                return;
            }

            // maximo de caracteres texto ingles
            if(txtingles.length > 300){
                toastr.error('Texto ingles máximo 300 caracteres');
                return;
            }

            openLoading();
            let formData = new FormData();
            formData.append('txtespanol', txtespanol);
            formData.append('txtingles', txtingles);

            axios.post('/admin/idiomasistema/nuevo', formData, {
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

        // informacion de un pais
        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/idiomasistema/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);
                        $('#espanol-editar').val(response.data.info.espanol);
                        $('#ingles-editar').val(response.data.info.ingles);

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        // editar datos de un pais
        function editar(){
            var id = document.getElementById('id-editar').value;
            var txtespanol = document.getElementById('espanol-editar').value;
            var txtingles = document.getElementById('ingles-editar').value;

            if(txtespanol === ''){
                toastr.error('Texto español es requerido');
                return;
            }

            if(txtingles === ''){
                toastr.error('Texto ingles es requerido');
                return;
            }

            // maximo de caracteres texto espanol
            if(txtespanol.length > 300){
                toastr.error('Texto español máximo 300 caracteres');
                return;
            }

            // maximo de caracteres texto ingles
            if(txtingles.length > 300){
                toastr.error('Texto ingles máximo 300 caracteres');
                return;
            }

            openLoading();
            let formData = new FormData();
            formData.append('id', id);
            formData.append('txtespanol', txtespanol);
            formData.append('txtingles', txtingles);

            axios.post('/admin/idiomasistema/actualizar', formData, {
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
