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

                <p style="font-weight: bold; font-size: 16px">Detalle del Devocional</p>

                <button type="button" onclick="vistaNuevoPlanBloqueDetalle()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo Detalle
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Devocional</li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Detalles</h3>
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

    <script type="text/javascript">
        $(document).ready(function(){
            var idplanbloque = {{ $idplanbloque }};
            var ruta = "{{ URL::to('/admin/planbloquedetalle/tabla') }}/" + idplanbloque;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            var idplanbloque = {{ $idplanbloque }};
            var ruta = "{{ URL::to('/admin/planbloquedetalle/tabla') }}/" + idplanbloque;
            $('#tablaDatatable').load(ruta);
        }

        // vista para agregar nuevo
        function vistaNuevoPlanBloqueDetalle(){
            var idplanbloque = {{ $idplanbloque }};
            window.location.href="{{ url('/admin/planbloquedetalle/agregar/nuevo/index') }}/" + idplanbloque;
        }

        // vista para editar
        function informacionEditar(idplanbloquedetalle){
            window.location.href="{{ url('/admin/planbloquedetalle/vista/editar/index') }}/" + idplanbloquedetalle;
        }

        // aqui se ingresara para agregar Texto Devocional
        function vistaDevocional(idplanbloquedetalle){
            window.location.href="{{ url('/admin/planbloquedetalle/devocional/vista') }}/" + idplanbloquedetalle;
        }

        // vistan listado de preguntas
        function vistaPreguntas(idplanbloquedetalle){
            window.location.href="{{ url('/admin/preguntas/vista') }}/" + idplanbloquedetalle;
        }



        function preguntaActivar(idplanbloquedetalle){

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
                    configurarPlan(1, idplanbloquedetalle);
                }
            })
        }


        function preguntaDeshabilitar(idplanbloquedetalle){

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
                    configurarPlan(0, idplanbloquedetalle);
                }
            })

        }


        function configurarPlan(estado, idplanbloquedetalle){

            let formData = new FormData();
            formData.append('idplanbloquedetalle', idplanbloquedetalle);
            formData.append('estado', estado);
            openLoading();

            axios.post('/admin/planbloquedetalle/activacion', formData, {
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
                            text: "Se requiere crear el Devocional",
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



    </script>


@endsection
