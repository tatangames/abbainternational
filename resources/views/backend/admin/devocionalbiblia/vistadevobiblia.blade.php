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
                <button type="button" onclick="modalNuevo()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo Registro
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Devocional Biblia</li>
                    <li class="breadcrumb-item active">Registros</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Biblias</h3>
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
                <h4 class="modal-title">Nueva Biblia</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-nuevo">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Biblia (Solo se puede registrar 1)</label>
                                        <select class="form-control" id="select-biblia">
                                            @foreach($arrayBiblias as $item)
                                                <option value="{{$item->id}}">{{$item->titulo}}</option>
                                            @endforeach
                                        </select>
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
            let idbloqedetalle = {{ $idbloqedetalle }};
            var ruta = "{{ URL::to('/admin/devobiblia/tabla') }}/" + idbloqedetalle;
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            let idbloqedetalle = {{ $idbloqedetalle }};
            var ruta = "{{ URL::to('/admin/devobiblia/tabla') }}/" + idbloqedetalle;
            $('#tablaDatatable').load(ruta);
        }


        function modalNuevo(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        // registrar biblia
        function nuevo(){

            var biblia = document.getElementById('select-biblia').value;

            if(biblia === '') {
                toastr.error('Biblia es requerido');
                return;
            }

            let idbloqedetalle = {{ $idbloqedetalle }};

            openLoading();

            var formData = new FormData();
            formData.append('idbiblia', biblia);
            formData.append('idbloqedetalle', idbloqedetalle);

            axios.post('/admin/devobiblia/registrar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        Swal.fire({
                            title: "No Registrado",
                            text: "Solo se puede 1 registro",
                            icon: 'info',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }

                    else if (response.data.success === 2) {
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



        function modalBorrar(id){

            Swal.fire({
                title: '¿Borrar?',
                text: '',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                allowOutsideClick: false,
                confirmButtonText: 'Si',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    borrarRegistro(id);
                }
            })
        }

        function borrarRegistro(id){

            openLoading();

            var formData = new FormData();
            formData.append('id', id);

            axios.post('/admin/devobiblia/borrarregistro', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        toastr.success('Borrado');
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




        function vistaCapitulo(idblockdetalle){
            window.location.href="{{ url('/admin/devobiblia/capitulos/vista') }}/" + idblockdetalle;
        }



    </script>


@endsection
