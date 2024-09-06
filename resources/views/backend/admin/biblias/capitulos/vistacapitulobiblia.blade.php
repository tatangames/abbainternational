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
                <button type="button" onclick="nuevoCapitulo()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo Registro
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Capítulos</li>
                    <li class="breadcrumb-item active">Listado de Capítulos</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Capítulos</h3>
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


    <!-- IDIOMAS DISPONIBLES PARA EDITAR TEXTO VERSICULO-->
    <div class="modal fade" id="modalIdioma" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Idioma Disponibles</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-idiomadispo">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <input type="hidden" id="idblocktexto">
                                    <label class="control-label">Idioma:</label>
                                    <select class="form-control" id="select-idioma">
                                    </select>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="buscarVersiculo()">Buscar</button>
                </div>
            </div>
        </div>
    </div>



    <!-- MODAL PARA AGREGAR DATOS EDITADOS -->

    <div class="modal fade" id="modalDatosEditados" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Versículo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-datoseditados">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Descripción </label>
                                    <textarea name="content" id="editor-editar"></textarea>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="actualizarVersiculo()">Actualizar</button>
                </div>
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
    <script src="{{ asset('plugins/ckeditor5v2/build/ckeditor.js') }}"></script>


    <script type="text/javascript">
        $(document).ready(function(){

            let idcapitulo = {{ $idcapitulo }};
            var ruta = "{{ URL::to('/admin/bibliacapitulo/bloque/tabla') }}/" + idcapitulo;
            $('#tablaDatatable').load(ruta);


            window.varGlobalEditorEditar;


            ClassicEditor
                .create(document.querySelector('#editor-editar'), {
                    language: 'es',
                })
                .then(editor => {
                    varGlobalEditorEditar = editor;
                })
                .catch(error => {

                });


            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            let idcapitulo = {{ $idcapitulo }};
            var ruta = "{{ URL::to('/admin/bibliacapitulo/bloque/tabla') }}/" + idcapitulo;
            $('#tablaDatatable').load(ruta);
        }


        function nuevoCapitulo(){
            var id = {{ $idcapitulo }};
            window.location.href="{{ url('/admin/capitulo/nuevo/index') }}/" + id;
        }

        function editarCapitulo(id){
            window.location.href="{{ url('/admin/capitulo/vista/editar') }}/" + id;
        }

        function informacionTexto(id){
            window.location.href="{{ url('/admin/capituloversiculo/editar') }}/" + id;
        }

        function preguntaActivar(idbloque){

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
                    configurar(1, idbloque);
                }
            })
        }

        function preguntaDeshabilitar(idbloque){

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
                    configurar(0, idbloque);
                }
            })
        }

        function configurar(estado, idbloque){

            let formData = new FormData();
            formData.append('idbloque', idbloque);
            formData.append('estado', estado);
            openLoading();

            axios.post('/admin/bibliacapitulo/bloque/activacion', formData, {
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


        function informacionTexto(id){
            // id: biblia_capitulo_bloque

            let formData = new FormData();
            formData.append('id', id);

            openLoading();

            axios.post('/admin/capitulo/idiomas/disponibles', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        // guardar id fila
                        // biblia_capitulo_bloque

                        $('#idblocktexto').val(id);

                        document.getElementById("select-idioma").options.length = 0;

                        $.each(response.data.listado, function( key, val ){
                            $('#select-idioma').append('<option value="' +val.ididioma +'">'+ val.idioma +'</option>');
                        });

                        $('#modalIdioma').modal('show');
                    }
                    else{
                        toastr.error('Error al buscar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al buscar');
                    closeLoading();
                });
        }

        function buscarVersiculo(){

            var idioma = document.getElementById('select-idioma').value;
            var id = document.getElementById('idblocktexto').value;

            $('#modalIdioma').modal('hide');

            let formData = new FormData();
            formData.append('id', id);
            formData.append('idioma', idioma);

            openLoading();

            axios.post('/admin/capitulo/idiomas/versiculo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        let texto = response.data.texto;
                        if(texto != null){
                            varGlobalEditorEditar.setData(texto);
                        }

                        $('#modalDatosEditados').css('overflow-y', 'auto');
                        $('#modalDatosEditados').modal({backdrop: 'static', keyboard: false})
                    }
                    else{
                        toastr.error('Error al buscar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al buscar');
                    closeLoading();
                });
        }


        function actualizarVersiculo(){

            var idioma = document.getElementById('select-idioma').value;
            var id = document.getElementById('idblocktexto').value;
            const versiculo = varGlobalEditorEditar.getData();

            if (versiculo.trim() === '') {
                toastr.error("Versículo es requerido");
                return;
            }

            $('#modalIdioma').modal('hide');

            let formData = new FormData();
            formData.append('id', id);
            formData.append('idioma', idioma);
            formData.append('versiculo', versiculo);

            openLoading();

            axios.post('/admin/capitulo/idiomas/versiculoactualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        toastr.success('Actualizado')

                        $('#modalDatosEditados').modal('hide');
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
