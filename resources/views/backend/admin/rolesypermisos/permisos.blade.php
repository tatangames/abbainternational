@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/responsive.bootstrap4.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons.bootstrap4.min.css') }}" type="text/css" rel="stylesheet" />
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
        <div class="container-fluid">
            <div class="col-sm-12">
                <h1>Permisos Administrador</h1>
            </div>
            <br>
            <button type="button" onclick="modalAgregar()" class="btn btn-success btn-sm">
                <i class="fas fa-pencil-alt"></i>
                Nuevo Administrador
            </button>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Lista</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="tablaDatatable"></div>
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
                    <h4 class="modal-title">Nuevo Administrador</h4>
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
                                        <input type="text" maxlength="50" autocomplete="off" class="form-control" id="nombre-nuevo" placeholder="Nombre">
                                    </div>

                                    <div class="form-group">
                                        <label>Correo</label>
                                        <input type="text" maxlength="100" autocomplete="off" class="form-control" id="correo-nuevo" placeholder="Correo electrónico">
                                    </div>

                                    <div class="form-group">
                                        <label>Contraseña</label>
                                        <input type="text" maxlength="16" autocomplete="off" class="form-control" id="password-nuevo" placeholder="Contraseña">
                                    </div>

                                    <div class="form-group">
                                        <label style="color:#191818">Rol</label>
                                        <br>
                                        <div>
                                            <select class="form-control" id="rol-nuevo">
                                                @foreach($roles as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="nuevoUsuario()">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Administrador</h4>
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
                                        <label style="color:#191818">Rol</label>
                                        <br>
                                        <div>
                                            <select class="form-control" id="rol-editar">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Nombre</label>
                                        <input type="hidden" id="id-editar">
                                        <input type="text" maxlength="50" autocomplete="off" class="form-control" id="nombre-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Correo electrónico</label>
                                        <input type="text" maxlength="100" autocomplete="off" class="form-control" id="correo-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Contraseña</label>
                                        <input type="text" maxlength="16" autocomplete="off" class="form-control" id="password-editar" placeholder="Contraseña">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizar()">Guardar</button>
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
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/jszip.min.js') }}"></script>
    <script src="{{ asset('js/buttons.html5.min.js') }}"></script>

    <!-- incluir tabla -->
    <script type="text/javascript">
        $(document).ready(function(){
            var ruta = "{{ URL::to('admin/permisos/tabla') }}";
            $('#tablaDatatable').load(ruta);
            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function nuevoUsuario(){

            var nombre = document.getElementById('nombre-nuevo').value;
            var correo = document.getElementById('correo-nuevo').value;
            var password = document.getElementById('password-nuevo').value;
            var idrol = document.getElementById('rol-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 50){
                toastr.error('Máximo 50 caracteres para Nombre');
                return;
            }

            if(correo === ''){
                toastr.error('Correo es requerido');
                return;
            }

            if(correo.length > 100){
                toastr.error('Máximo 100 caracteres para Correo');
                return;
            }

            if(password === ''){
                toastr.error('Contraseña es requerido');
                return;
            }

            if(password.length < 4){
                toastr.error('Mínimo 4 caracteres para contraseña');
                return;
            }

            if(password.length > 16){
                toastr.error('Máximo 16 caracteres para contraseña');
                return;
            }

            if(idrol === ''){
                toastr.error('Rol es requerido');
                return;
            }

            openLoading();
            let formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('correo', correo);
            formData.append('password', password);
            formData.append('rol', idrol);



            axios.post('/admin/permisos/nuevo-usuario', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.error('Correo ya registrado');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Agregado');
                        $('#modalAgregar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch((error) => {
                    closeLoading()
                    toastr.error('Error al guardar');
                });
        }

        function verInformacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/permisos/info-usuario',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);
                        $('#nombre-editar').val(response.data.info.nombre);
                        $('#correo-editar').val(response.data.info.email);

                        document.getElementById("rol-editar").options.length = 0;

                        $.each(response.data.roles, function( key, val ){

                            if(response.data.idrol[0] == key){
                                $('#rol-editar').append('<option value="' +key +'" selected="selected">'+val+'</option>');
                            }else{
                                $('#rol-editar').append('<option value="' +key +'">'+val+'</option>');
                            }
                        });


                        if(response.data.info.activo === 0){
                            $("#toggle-editar").prop("checked", false);
                        }else{
                            $("#toggle-editar").prop("checked", true);
                        }

                    }else{
                        toastr.error('Información no encontrado.');
                    }

                })
                .catch((error) => {
                    closeLoading()
                    toastr.error('Información no encontrado..');
                });
        }

        function actualizar(){
            var id = document.getElementById('id-editar').value;
            var nombre = document.getElementById('nombre-editar').value;
            var correo = document.getElementById('correo-editar').value;
            var password = document.getElementById('password-editar').value;
            var idrol = document.getElementById('rol-editar').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 50){
                toastr.error('Máximo 50 caracteres para nombre');
                return;
            }

            if(correo === ''){
                toastr.error('Correo es requerido');
                return;
            }

            if(correo.length > 100){
                toastr.error('Máximo 100 caracteres para correo');
                return;
            }

           if(password.length > 0){
               if(password.length < 4){
                   toastr.error('Mínimo 4 caracteres para contraseña');
                   return;
               }

               if(password.length > 16){
                   toastr.error('Máximo 16 caracteres para contraseña');
                   return;
               }
           }


            openLoading()
            var formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('correo', correo);
            formData.append('password', password);
            formData.append('rol', idrol);

            axios.post('/admin/permisos/editar-usuario', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.error('El Correo ya existe');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Actualizado');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al actualizar');
                });
        }

        function recargar(){
            var ruta = "{{ url('/admin/permisos/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

    </script>


@stop
