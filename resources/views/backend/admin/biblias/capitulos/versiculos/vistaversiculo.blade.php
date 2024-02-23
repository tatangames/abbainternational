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
                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo versículo
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Versículo</li>
                    <li class="breadcrumb-item active">Listado</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Listado de Versículos</h3>
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
                    <h4 class="modal-title">Nuevo Versículo</h4>
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
                                        <input type="text" maxlength="50" class="form-control" id="titulo-nuevo" autocomplete="off">
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

    <!-- modal editar -->
    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog">
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
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="hidden" id="id-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Versículo</label>
                                        <input type="text" maxlength="50" class="form-control" id="titulo-editar" autocomplete="off">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editar()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalEditarTexto" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Texto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-editar">
                    <div class="card-body">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label>Versículo</label>
                                <input type="hidden" id="idfila-texto">
                                <textarea name="content" id="texto-editor" rows="12" cols="50"></textarea>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="miboton" onclick="guardarTexto()">Guardar</button>
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

            window.varGlobalTexto;

            ClassicEditor
                .create(document.querySelector('#texto-editor'), {
                    language: 'es',
                })
                .then(editor => {
                    varGlobalTexto = editor;
                })
                .catch(error => {

                });

            let idbloque = {{ $idbloque }};
            var ruta = "{{ URL::to('/admin/bibliacapitulo/versiculo/tabla') }}/" + idbloque;
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";

        });
    </script>

    <script>

        // recarga tabla
        function recargar(){
            let idbloque = {{ $idbloque }};
            var ruta = "{{ URL::to('/admin/bibliacapitulo/versiculo/tabla') }}/" + idbloque;
            $('#tablaDatatable').load(ruta);
        }

        // abre modal para agregar nuevo pais
        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        // envia datos de nuevo pais al servidor
        function nuevo(){
            var titulo = document.getElementById('titulo-nuevo').value;

            if(titulo === ''){
                toastr.error('Título es requerido');
                return;
            }

            let idbloque = {{ $idbloque }};

            openLoading();
            let formData = new FormData();
            formData.append('idbloque', idbloque);
            formData.append('titulo', titulo);

            axios.post('/admin/bibliacapitulo/versiculo/registrar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
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

        // informacion de un pais
        function informacionEditar(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/bibliacapitulo/versiculo/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);
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


        // editar datos de un pais
        function editar(){
            var id = document.getElementById('id-editar').value;
            var titulo = document.getElementById('titulo-editar').value;

            if(titulo === ''){
                toastr.error('Título es requerido');
                return;
            }

            openLoading();
            let formData = new FormData();
            formData.append('idversiculo', id);
            formData.append('titulo', titulo);

            axios.post('/admin/bibliacapitulo/versiculo/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
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

            axios.post('/admin/bibliacapitulo/versiculo/activacion', formData, {
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


        // buscar texto si existe
        function vistaTextos(idversiculo){

            let formData = new FormData();
            formData.append('idversiculo', idversiculo);
            openLoading();

            axios.post('/admin/buscar/texto/versiculo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        let haydatos = response.data.hayguardado;
                        let texto = response.data.texto;

                        $('#idfila-texto').val(idversiculo);

                        varGlobalTexto.setData(texto);

                        if(haydatos == 1){
                          document.getElementById("miboton").textContent = "Actualizar";
                        }else{
                          document.getElementById("miboton").textContent = "Guardar";
                        }

                        $('#modalEditarTexto').css('overflow-y', 'auto');
                        $('#modalEditarTexto').modal({backdrop: 'static', keyboard: false})
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


        function guardarTexto(){

            var idversiculo = document.getElementById('idfila-texto').value;

            const contenido = varGlobalTexto.getData();

            if (contenido.trim() === '') {
                toastr.error("Texto es requerido");
                return;
            }

            let formData = new FormData();
            formData.append('idversiculo', idversiculo);
            formData.append('contenido', contenido);
            openLoading();

            axios.post('/admin/guardar/texto/versiculo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado');

                        $('#modalEditarTexto').modal('hide');
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
