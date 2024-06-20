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

                <p style="font-weight: bold; font-size: 16px">Preguntas para Devocional</p>

                <button type="button" onclick="vistaNuevasPreguntas()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nueva Pregunta
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Devocional</li>
                    <li class="breadcrumb-item active">Preguntas</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Preguntas</h3>
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


</div>


@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery-ui-drag.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-drag.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('plugins/ckeditor5v1/build/ckeditor.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var idplanbloquedetalle = {{ $idplanbloquedetalle }};
            var ruta = "{{ URL::to('/admin/preguntas/tabla') }}/" + idplanbloquedetalle;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function recargar(){
            var idplanbloquedetalle = {{ $idplanbloquedetalle }};
            var ruta = "{{ URL::to('/admin/preguntas/tabla') }}/" + idplanbloquedetalle;
            $('#tablaDatatable').load(ruta);
        }


        function vistaNuevasPreguntas(){
            let idplanbloquedetalle = {{ $idplanbloquedetalle }};
            window.location.href="{{ url('/admin/preguntas/nuevoregitros') }}/" + idplanbloquedetalle;
        }


        function informacionEditar(idbloquepregunta){
            window.location.href="{{ url('/admin/preguntas/vista/editar') }}/" + idbloquepregunta;
        }

        function modalBorrar(id){

            Swal.fire({
                title: '¿Borrar?',
                text: 'Esto eliminara las preguntas contestadas por el Usuario, si ya las ha respondido',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                allowOutsideClick: false,
                confirmButtonText: 'Si',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    apiBorrarPregunta(id);
                }
            })
        }


        function apiBorrarPregunta(id){


            let formData = new FormData();
            formData.append('id', id);
            openLoading();

            axios.post('/admin/preguntas/borrar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Borrado');
                        recargar();
                    }
                    else {
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
