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

                <p style="font-weight: bold; font-size: 16px">Devocional: {{$nombreDevo}}</p>

                <button type="button" onclick="vistaNuevoPlanBloque()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nueva Fecha
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Devocional</li>
                    <li class="breadcrumb-item active">Fechas</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Fechas para Devocional</h3>
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

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var idplan = {{ $idplan }};
            var ruta = "{{ URL::to('/admin/planesbloques/tabla/index') }}/" + idplan;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            var idplan = {{ $idplan }};
            var ruta = "{{ URL::to('/admin/planesbloques/tabla/index') }}/" + idplan;
            $('#tablaDatatable').load(ruta);
        }


        function vistaNuevoPlanBloque(){
            var idplan = {{ $idplan }};
            window.location.href="{{ url('/admin/planesbloques/agregar/nuevo/index') }}/" + idplan;
        }

        function informacionEditar(idplanbloque){
            window.location.href="{{ url('/admin/planesbloques/vista/editar/index') }}/" + idplanbloque;
        }

        // aqui se ingresara a ver el detalle de ese bloque
        function informacionDetalleBloque(idplanbloque){
            window.location.href="{{ url('/admin/planbloquedetalle/vista') }}/" + idplanbloque;
        }


        function modalBorrar(idplanbloques){

            Swal.fire({
                title: '¿Borrar?',
                text: "Borrar registro",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    apiBorrar(idplanbloques);
                }
            })
        }


        function apiBorrar(idplanbloques){

            let formData = new FormData();
            formData.append('idplanbloque', idplanbloques);
            openLoading();

            axios.post('/admin/planesbloques/borrarregistro', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        toastr.success('Actualizado');
                        recargar();
                    }
                    else  if(response.data.success === 2){

                        Swal.fire({
                            title: 'No Activado',
                            text: "Se requiere crear el Devociona a esta Fecha",
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
