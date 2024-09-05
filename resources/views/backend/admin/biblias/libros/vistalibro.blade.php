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
            <button type="button" onclick="nuevoLibro()" class="btn btn-primary btn-sm">
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

        function nuevoLibro(){
            var id = {{ $idbiblia }};
            window.location.href="{{ url('/admin/biblialibro/nuevo/index') }}/" + id;
        }

        function editarLibro(id){
            window.location.href="{{ url('/admin/biblialibro/vista/editar') }}/" + id;
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
