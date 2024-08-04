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
                    Nuevo Recurso
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Recursos</li>
                    <li class="breadcrumb-item active">Web</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Recursos</h3>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo recurso</h4>
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
                                    <label>Nombre</label>
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="nombre-nuevo" placeholder="Nombre">
                                </div>


                                <div class="form-group">
                                    <div>
                                        <label>Imagen</label>
                                    </div>
                                    <br>
                                    <div class="col-md-10">
                                        <input type="file" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" maxlength="800" autocomplete="off" class="form-control" id="link-nuevo" placeholder="Link">
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




<!-- modal editar-->
<div class="modal fade" id="modalEditar" >
    <div class="modal-dialog modal-lg">
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
                        <div class="col-md-12">


                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="hidden" id="id-editar" >
                                <input type="text" maxlength="100" autocomplete="off" class="form-control" id="nombre-editar" placeholder="Nombre">
                            </div>


                            <div class="form-group">
                                <div>
                                    <label>Imagen</label>
                                </div>
                                <br>
                                <div class="col-md-10">
                                    <input type="file" style="color:#191818" id="imagen-editar" accept="image/jpeg, image/jpg, image/png"/>
                                </div>
                            </div>


                            <div class="form-group">
                                <label>Link</label>
                                <input type="text" maxlength="800" autocomplete="off" class="form-control" id="link-editar" placeholder="Link">
                            </div>



                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="editar()">Actualizar</button>
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

    <script type="text/javascript">
        $(document).ready(function(){
            var ruta = "{{ URL::to('/admin/recursosweb/tabla') }}";
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ URL::to('/admin/recursosweb/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // abrir modal
        function modalNuevo(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        //nuevo servicio
        function nuevo(){

            var nombre = document.getElementById('nombre-nuevo').value;
            var link = document.getElementById('link-nuevo').value;
            var imagen = document.getElementById('imagen-nuevo');

            if(nombre === '') {
                toastr.error('Nombre es requerido');
                return;
            }

            if(link === '') {
                toastr.error('Link es requerido');
                return;
            }

            if(imagen.files && imagen.files[0]){ // si trae imagen
                if (!imagen.files[0].type.match('image/jpeg|image/jpeg|image/png')){
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }else{
                toastr.error('Imagen es Requerida')
                return;
            }


            openLoading();


            var formData = new FormData();
            formData.append('imagen', imagen.files[0]);
            formData.append('nombre', nombre);
            formData.append('link', link);

            axios.post('/admin/recursosweb/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
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
                title: 'Borrar Red?',
                text: "",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    solicitarBorrarImagen(id);
                }
            })
        }

        function solicitarBorrarImagen(id){

            openLoading();

            axios.post('/admin/recursosweb/borrar',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        toastr.success('Red Eliminado');
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



        function informacion(id){

            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/recursosweb/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');

                        $('#id-editar').val(id);
                        $('#nombre-editar').val(response.data.info.nombre);
                        $('#link-editar').val(response.data.info.link);

                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }

        function editar(){

            var id = document.getElementById('id-editar').value;
            var nombre = document.getElementById('nombre-editar').value;
            var link = document.getElementById('link-editar').value;
            var imagen = document.getElementById('imagen-editar');

            if(nombre === '') {
                toastr.error('Nombre es requerido');
                return;
            }

            if(link === '') {
                toastr.error('Link es requerido');
                return;
            }

            if(imagen.files && imagen.files[0]){ // si trae imagen
                if (!imagen.files[0].type.match('image/jpeg|image/jpeg|image/png')){
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }

            openLoading();

            var formData = new FormData();
            formData.append('id', id);
            formData.append('imagen', imagen.files[0]);
            formData.append('nombre', nombre);
            formData.append('link', link);

            axios.post('/admin/recursosweb/editar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#modalEditar').modal('hide');
                        toastr.success('Actualizado correctamente');
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


    </script>


@endsection
