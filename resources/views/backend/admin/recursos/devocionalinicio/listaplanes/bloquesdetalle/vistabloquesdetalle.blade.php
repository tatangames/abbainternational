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
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Listado de Devocionales</li>
                    <li class="breadcrumb-item active">Información</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Devocional</h3>
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


<div class="modal fade" id="modalDevocional" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Información</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-devocional">
                    <div class="card-body">
                        <div class="col-md-12">


                            <div class="form-group">
                                <label>Devocional</label>
                                <textarea name="content" id="editor"></textarea>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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
    <script src="{{ asset('plugins/ckeditor5v1/build/ckeditor.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var idplanbloque = {{ $idplanbloque }};

            window.varGlobalEditorNuevo;

            ClassicEditor
                .create(document.querySelector('#editor'), {
                    language: 'es',
                })
                .then(editor => {
                    varGlobalEditorNuevo = editor;
                })
                .catch(error => {

                });

            var ruta = "{{ URL::to('/admin/devoinicio/planes/bloquestabladetalle') }}/" + idplanbloque;
            $('#tablaDatatable').load(ruta);
        });


    </script>

    <script>

        // VER TEXTO DEVOCIONAL EN MODAL
        function infoVerDevocional(idplanesblockdetalle){

            openLoading();

            let formData = new FormData();

            formData.append('idplanesblockdetalle', idplanesblockdetalle);

            axios.post('/admin/devoinicio/informacion/devocional', formData, {
            })
                .then((response) => {
                    closeLoading();


                    if(response.data.success === 1){

                        if(response.data.texto == null){
                            varGlobalEditorNuevo.setData("");
                        }else{
                            varGlobalEditorNuevo.setData(response.data.texto);
                        }

                        $('#modalDevocional').modal('show');
                    }
                    else if(response.data.success === 2) {
                        varGlobalEditorNuevo.setData("");
                        toastr.info('Devocional no encontrado');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al actualizar');
                    closeLoading();
                });
        }

        // REGISTRARLO
        function registrarLecturaDia(idplanesblockdetalle){

            openLoading();

            let formData = new FormData();

            formData.append('idplanesblockdetalle', idplanesblockdetalle);

            axios.post('/admin/devoinicio/seleccionar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        var fecha = response.data.fecha;

                        Swal.fire({
                            title: "No Registrado",
                            text: "Ya se encuentra una misma fecha registrada: " + fecha,
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
                    else if(response.data.success === 2) {
                        toastr.success('Registrado');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
                    closeLoading();
                });
        }


    </script>


@endsection
