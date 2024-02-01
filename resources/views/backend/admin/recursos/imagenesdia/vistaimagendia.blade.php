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
                    Nueva Imagen
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Recursos</li>
                    <li class="breadcrumb-item active">Imágenes</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title" style="color: white">Lista de Imágenes</h3>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nueva Imagen</h4>
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
                                    <label>Descripción</label>
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="descripcion-nuevo" placeholder="Descripción">
                                </div>


                                <div class="form-group">
                                    <div>
                                        <label>Imagen</label>
                                        <p>Tamaño no superar: 1000 x 1000 px</p>
                                    </div>
                                    <br>
                                    <div class="col-md-10">
                                        <input type="file" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                    </div>
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
                <h4 class="modal-title">Editar Imagen</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-editar">
                    <div class="card-body">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label>Descripción</label>
                                <input type="hidden" id="id-editar">
                                <input type="text" maxlength="100" autocomplete="off" class="form-control" id="descripcion-editar">
                            </div>

                            <div class="form-group">
                                <div>
                                    <label>Imagen</label>
                                    <p>Tamaño no superar: 1000 x 1000 px</p>
                                </div>
                                <br>
                                <div class="col-md-10">
                                    <input type="file" style="color:#191818" id="imagen-editar" accept="image/jpeg, image/jpg, image/png"/>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="editar()">Guardar</button>
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
            var ruta = "{{ URL::to('/admin/imagendia/tabla') }}";
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ URL::to('/admin/imagendia/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        // abrir modal
        function modalNuevo(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        //nuevo servicio
        function nuevo(){

            var descripcion = document.getElementById('descripcion-nuevo').value;
            var imagen = document.getElementById('imagen-nuevo');

            if(descripcion === '') {
                toastr.error('Descripción es requerido');
                return;
            }

            if(descripcion.length > 100){
                toastr.error('Descripción máximo 100 caracteres');
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
            formData.append('descripcion', descripcion);

            axios.post('/admin/imagendia/nuevo', formData, {
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


        function modalBorrar(idimagen){
            Swal.fire({
                title: 'Borrar Imagen?',
                text: "",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    solicitarBorrarImagen(idimagen);
                }
            })
        }

        function solicitarBorrarImagen(idimagen){

            openLoading();

            axios.post('/admin/imagendia/borrar',{
                'idimagen': idimagen
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        toastr.success('Imagen Eliminada');
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

            document.getElementById("formulario-editar").reset();
            openLoading();

            axios.post('/admin/productos/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);
                        $('#nombre-editar').val(response.data.producto.nombre);
                        $('#descripcion-editar').val(response.data.producto.descripcion);
                        $('#precio-editar').val(response.data.producto.precio);
                        $('#nota-editar').val(response.data.producto.nota);

                        if(response.data.producto.activo === 0){
                            $("#toggle-activo").prop("checked", false);
                        }else{
                            $("#toggle-activo").prop("checked", true);
                        }

                        if(response.data.producto.utiliza_nota === 0){
                            $("#toggle-nota-editar").prop("checked", false);
                        }else{
                            $("#toggle-nota-editar").prop("checked", true);
                        }

                        if(response.data.producto.utiliza_imagen === 0){
                            $("#toggle-imagen-editar").prop("checked", false);
                        }else{
                            $("#toggle-imagen-editar").prop("checked", true);
                        }

                        document.getElementById("select-mover").options.length = 0;

                        $.each(response.data.arraysub, function( key, val ){
                            if(response.data.producto.id_subcategorias == val.id){
                                $('#select-mover').append('<option value="' +val.id +'" selected="selected">'+val.nombre+'</option>');
                            }else{
                                $('#select-mover').append('<option value="' +val.id +'">'+val.nombre+'</option>');
                            }
                        });

                    }else{
                        toastr.error('Error al buscar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al buscar');
                    closeLoading();
                });
        }

        function editar(){

            var id = document.getElementById('id-editar').value;
            var nombre = document.getElementById('nombre-editar').value;
            var descripcion = document.getElementById('descripcion-editar').value;
            var precio = document.getElementById('precio-editar').value;
            var imagen = document.getElementById('imagen-editar');
            var cbimagen = document.getElementById('toggle-imagen-editar').checked;
            var cbnota = document.getElementById('toggle-nota-editar').checked;
            var cbactivo = document.getElementById('toggle-activo').checked;
            var nota = document.getElementById('nota-editar').value;

            // para mover de sub categorias
            var idsubcate = document.getElementById('select-mover').value;

            var check_imagen = cbimagen ? 1 : 0;
            var check_nota = cbnota ? 1 : 0;
            var check_activo = cbactivo ? 1 : 0;

            if(idsubcate === '') {
                toastr.error('ID Sub categoría es requerido');
                return;
            }

            if(nombre === '') {
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 150){
                toastr.error('Nombre máximo 150 caracteres');
                return;
            }

            if(nota.length > 500){
                toastr.error('Nota máximo 500 caracteres');
                return;
            }

            if(descripcion.length > 2000){
                toastr.error('Descripción máximo 2000 caracteres');
                return;
            }

            var reglaNumeroDecimal = /^[0-9]\d*(\.\d+)?$/;

            if(precio === ''){
                toastr.error('Precio es requerido');
                return;
            }

            if(!precio.match(reglaNumeroDecimal)) {
                toastr.error('Precio debe ser número decimal');
                return;
            }

            if(precio < 0){
                toastr.error('Precio no debe ser negativo');
                return;
            }

            if(precio > 1000000){
                toastr.error('Máximo 1 millón');
                return;
            }

            if(imagen.files && imagen.files[0]){ // si trae imagen
                if (!imagen.files[0].type.match('image/jpeg|image/jpeg|image/png')){
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }

            if(check_nota === 1){
                if(nota === ''){
                    toastr.error('Nota es requerida si se utilizara');
                    return;
                }
            }

            openLoading();

            var formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('imagen', imagen.files[0]);
            formData.append('descripcion', descripcion);
            formData.append('precio', precio);
            formData.append('cbnota', check_nota);
            formData.append('cbimagen', check_imagen);
            formData.append('cbactivo', check_activo);
            formData.append('nota', nota);
            formData.append('idsubcate', idsubcate);


            axios.post('/admin/productos/editar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#modalEditar').modal('hide');
                        toastr.success('Actualizado correctamente');
                        recargar();
                    }
                    else if (response.data.success === 3) {
                        toastr.error('No se puede utilizar imagen sino hay una guardada');
                        recargar();
                    }
                    else {
                        toastr.error('Error al Editar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al Editar');
                    closeLoading();
                });
        }

        function verCategorias(id) {
            window.location.href="{{ url('/admin/categorias/') }}/"+id;
        }

    </script>


@endsection
