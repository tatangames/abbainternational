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

                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nueva Nivel
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Insignias</li>
                    <li class="breadcrumb-item active">Niveles</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Niveles</h3>
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
                                        <label>Nivel</label>
                                        <input type="number" class="form-control" id="nivel-nuevo" autocomplete="off">
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
            var idtipoinsignia = {{ $idtipoinsignia }};
            var ruta = "{{ URL::to('/admin/tipoinsignias/vista/tablaniveles') }}/" + idtipoinsignia;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            var idtipoinsignia = {{ $idtipoinsignia }};
            var ruta = "{{ URL::to('/admin/tipoinsignias/vista/tablaniveles') }}/" + idtipoinsignia;
            $('#tablaDatatable').load(ruta);
        }

        // abre modal para agregar nuevo iglesia
        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        // envia datos de nueva iglesia al servidor
        function nuevo(){
            var nivel = document.getElementById('nivel-nuevo').value;

            if(nivel === ''){
                toastr.error('Nivel es requerida');
                return;
            }


            var reglaNumeroEntero = /^[0-9]\d*$/;


            if(!nivel.match(reglaNumeroEntero)) {
                toastr.error('Nivel número Entero y no Negativo');
                return;
            }

            if(nivel <= 0){
                toastr.error('Nivel no debe ser negativo o cero');
                return;
            }

            if(nivel > 9000000){
                toastr.error('Nivel no debe ser mayor 9 millones');
                return;
            }


            var idtipoinsignia = {{ $idtipoinsignia }};

            openLoading();
            let formData = new FormData();
            formData.append('idtipoinsignia', idtipoinsignia);
            formData.append('nivel', nivel);

            axios.post('/admin/tipoinsignias/niveles/registrar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    console.log(response)

                    if(response.data.success === 1){
                        toastr.error('El nivel ya esta registrado');
                    }

                    else if(response.data.success === 2){

                        Swal.fire({
                            title: "No Guardado",
                            text: "El nivel a registrar no debe ser igual o menor a los ya registrados",
                            icon: 'question',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {

                            }
                        })
                    }
                    else if(response.data.success === 3){
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


    </script>


@endsection
