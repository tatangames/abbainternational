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

        <div class="col-sm-6">
            <p style="font-weight: bold">Biblia: {{ $nombre }}</p>
        </div>

        <div class="col-sm-6">
            <button type="button" onclick="nuevoCapitulo()" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-square"></i>
                Nuevo Libro
            </button>
        </div>

    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Libros</h3>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Libro</h4>
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
                                    <label>Título</label>
                                    <input type="text" maxlength="50" autocomplete="off" class="form-control" id="titulo-nuevo" placeholder="Título">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="registrar()">Guardar</button>
            </div>
        </div>
    </div>
</div>



<!-- modal editar-->
<div class="modal fade" id="modalEditar" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-editar">
                    <div class="card-body">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label>Título</label>
                                <input type="hidden" id="id-editar">
                                <input type="text" maxlength="50" autocomplete="off" class="form-control" id="titulo-editar">
                            </div>


                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="editar()">Actualizar</button>
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

            var idbiblia = {{ $idbiblia }};
            var ruta = "{{ URL::to('/admin/biblialibro/tabla') }}/" + idbiblia;
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            var idbiblia = {{ $idbiblia }};
            var ruta = "{{ URL::to('/admin/biblialibro/tabla') }}/" + idbiblia;
            $('#tablaDatatable').load(ruta);
        }

        function nuevoCapitulo(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }


        function registrar(){

            var titulo = document.getElementById('titulo-nuevo').value;

            if(titulo === '') {
                toastr.error('Título es requerido');
                return;
            }

            openLoading();

            let idbiblia = {{ $idbiblia }};

            var formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('idbiblia', idbiblia);

            axios.post('/admin/biblialibro/registrar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#modalAgregar').modal('hide');
                        toastr.success('Registrado correctamente');
                        recargar();
                    }
                    else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al guardar');
                });
        }




        function informacionEditar(id){

            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/biblialibro/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);
                        $('#titulo-editar').val(response.data.titulo);

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        function editar(){

            var id = document.getElementById('id-editar').value;
            var titulo = document.getElementById('titulo-editar').value;

            if(titulo === '') {
                toastr.error('Título es requerido');
                return;
            }

            openLoading();

            var formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('idcapitulo', id);

            axios.post('/admin/biblialibro/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#modalEditar').modal('hide');
                        toastr.success('Actualizado correctamente');
                        recargar();
                    }
                    else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al guardar');
                });
        }



        function preguntaActivar(idcapitulo){

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
                    configurar(1, idcapitulo);
                }
            })
        }


        function preguntaDeshabilitar(idcapitulo){

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
                    configurar(0, idcapitulo);
                }
            })
        }


        function configurar(estado, idcapitulo){

            let formData = new FormData();
            formData.append('idcapitulo', idcapitulo);
            formData.append('estado', estado);
            openLoading();

            axios.post('/admin/biblialibro/activacion', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado');
                        recargar();
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


        function infoBloque(idcapitulo){
            window.location.href="{{ url('/admin/bibliacapitulo/bloque/vista') }}/" + idcapitulo;
        }

    </script>


@endsection
