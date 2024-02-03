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
                <button type="button" onclick="vistaNuevoVideo()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo URL Video
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Videos</li>
                    <li class="breadcrumb-item active">Videos URL</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Videos</h3>
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
            var ruta = "{{ URL::to('/admin/videoshoy/tabla') }}";
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>


        function recargar(){
            var ruta = "{{ URL::to('/admin/videoshoy/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // vista para agregar nuevo video
        function vistaNuevoVideo(){
            window.location.href="{{ url('/admin/videoshoy/vista/nuevo') }}";
        }

        function vistaEditarVideoHoy(idvideohoy){
            window.location.href="{{ url('/admin/videoshoy/vista/editar') }}/" + idvideohoy;
        }

        function informacionBorrar(idvideoshoy){
            Swal.fire({
                title: 'Borrar URL?',
                text: "",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    borrarVideoUrl(idvideoshoy);
                }
            })
        }

        function borrarVideoUrl(idvideoshoy){

            openLoading();

            axios.post('/admin/videoshoy/borrar',{
                'idvideohoy': idvideoshoy
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        toastr.success('URL Eliminada');
                        recargar();
                    }else{
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
