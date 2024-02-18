@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }

    .widget-user-image2{
        left:50%;margin-left:-45px;
        position:absolute;
        top:80px
    }


    .widget-user-image2>img{
        border:3px solid #fff;
        height:auto;
    }

</style>


<div id="divcontenedor" style="display: none">


    <section class="content" style="margin-top: 20px">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Editar</h3>
                </div>
                <div class="card-body">
                    <div>
                        <div class="col-md-12">

                            <section>
                                <label class="control-label">Imagen (Ejemplo 600x400 px)</label>

                                <div class="row">

                                    <div class="input-group input-group col-md-4">
                                        <input type="file" class="form-control" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>

                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-info btn-sm" onclick="actualizarImagen()">Actualizar</button>
                                            </span>
                                    </div>

                                </div>

                            </section>




                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Información</h3>
                </div>

                <table class="table" id="matriz" data-toggle="table" style="margin-right: 15px; margin-left: 15px;">
                    <thead>
                    <tr>
                        <th style="width: 4%">#</th>
                        <th style="width: 10%">Idioma</th>
                        <th style="width: 10%">Título</th>
                        <th style="width: 10%">Subtitulo</th>
                        <th style="width: 6%">Opciones</th>
                    </tr>
                    </thead>
                    <tbody>


                    @foreach($arrayComparteAppTextos as $item)
                        <tr>
                            <td>
                                <p id="fila" class="form-control" style="max-width: 65px">{{ $item->contador }}</p>
                            </td>

                            <td>
                                <!-- data-ididioma se utiliza para comparar si falta agregar idioma nuevo -->
                                <input name="arrayIdioma[]" disabled data-idfila="{{ $item->id }}" data-ididioma="{{ $item->id_idioma_planes }}" value="{{ $item->idioma }}" class="form-control" type="text">
                            </td>

                            <td>
                                <input name="arrayTitulo[]" disabled maxlength="100"  value="{{ $item->texto_1 }}" class="form-control" type="text">
                            </td>

                            <td>
                                <input name="arraySubtitulo[]" disabled maxlength="200"  value="{{ $item->texto_2 }}" class="form-control" type="text">
                            </td>

                            <td>
                                <button type="button" class="btn btn-block btn-info" onclick="editarFila(this)">Editar</button>
                            </td>
                        </tr>

                    @endforeach


                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- MODAL PARA AGREGAR DATOS DE UN IDIOMA -->

    <div class="modal fade" id="modalDatosIdioma" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Información</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-datosidioma">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Título</label>
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="titulo-nuevo">
                                </div>

                                <div class="form-group">
                                    <label>Subtitulo</label>
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="subtitulo-nuevo">
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="AgregarFila()">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- MODAL PARA AGREGAR DATOS EDITADOS -->

    <div class="modal fade" id="modalDatosEditados" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Información</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-datoseditados">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Título</label>
                                    <input type="hidden" id="id-editar">
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="titulo-editar">
                                </div>

                                <div class="form-group">
                                    <label>Subtitulo</label>
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="subtitulo-editar">
                                </div>


                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="actualizarDatosEditados()">Actualizar Fila</button>
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
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('plugins/ckeditor5v1/build/ckeditor.js') }}"></script>


    <script type="text/javascript">
        $(document).ready(function() {

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        var referenciaArrayTitulo;
        var referenciaArraySubtitulo;

        // obtener los datos de la fila y llevarlos al modal
        function editarFila(e){

            document.getElementById("formulario-datoseditados").reset();


            var fila = $(e).closest('tr');


            var valorInputFila = fila.find('input[name="arrayIdioma[]"]');
            var valorActualFila = valorInputFila.data('idfila'); // ESTE ES EL DATA-
            $('#id-editar').val(valorActualFila);


            var valorInputTitulo = fila.find('input[name="arrayTitulo[]"]').val();
            var valorInputTituloRef = fila.find('input[name="arrayTitulo[]"]');
            referenciaArrayTitulo = valorInputTituloRef;

            var valorInputSubtitulo = fila.find('input[name="arraySubtitulo[]"]').val();
            var valorInputSubtituloRef = fila.find('input[name="arraySubtitulo[]"]');
            referenciaArraySubtitulo = valorInputSubtituloRef;

            // limpiar modal

            $('#titulo-editar').val(valorInputTitulo);
            $('#subtitulo-editar').val(valorInputSubtitulo);

            $('#modalDatosEditados').modal('show');
        }

        // ACTUALIZAR LA FILA
        function actualizarDatosEditados(){

            var idfila = document.getElementById('id-editar').value;
            var titulo = document.getElementById('titulo-editar').value;
            var subtitulo = document.getElementById('subtitulo-editar').value;

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            if(titulo.length > 100){
                toastr.error('Título 100 caracteres máximo')
                return;
            }

            if(subtitulo.length > 200){
                toastr.error('Título 200 caracteres máximo')
                return;
            }


            let formData = new FormData();
            formData.append('idfila', idfila);
            formData.append('titulo',titulo);
            formData.append('subtitulo', subtitulo);

            openLoading();

            axios.post('/admin/comparteapp/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        Swal.fire({
                            title: "Actualizado",
                            text: "",
                            icon: 'success',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
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


        // verificar que idioma no este en la tabla, para abrir modal y agrgear el nuevo idioma a la fila
        function verificarIdiomaTabla(){

            var idIdiomaSelect = document.getElementById('select-idioma').value;

            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();

            for(var a = 0; a < arrayIdIdioma.length; a++){

                let infoIdLenguaje = arrayIdIdioma[a];

                if(idIdiomaSelect == infoIdLenguaje){
                    toastr.error('El Idioma ya esta agregado');
                    return;
                }
            }

            // puede abrir modal para registrar datos

            document.getElementById("formulario-datosidioma").reset();

            $('#modalDatosIdioma').modal('show');
        }


        // actualizar la imagen
        function actualizarImagen(){

            var imagen = document.getElementById('imagen-nuevo');

            if(imagen.files && imagen.files[0]){ // si trae imagen
                if (!imagen.files[0].type.match('image/jpeg|image/jpeg|image/png')){
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }else{
                toastr.error('Imagen es requerida');
                return;
            }

            let formData = new FormData();
            formData.append('imagen', imagen.files[0]);

            openLoading();

            axios.post('/admin/comparteapp/actualizar/imagen', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        toastr.success("Actualizado");
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




        // CUANDO ES NUEVO TEXTO, para nuevo idioma
        function AgregarFila(){

            // verificar siempre
            var idIdiomaSelect = document.getElementById('select-idioma').value;

            let selectElement = document.getElementById("select-idioma");
            let selectedIndex = selectElement.selectedIndex;
            let selectedOptionText = selectElement.options[selectedIndex].text;

            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();

            for(var a = 0; a < arrayIdIdioma.length; a++){

                let infoIdIdioma = arrayIdIdioma[a];

                if(idIdiomaSelect == infoIdIdioma){
                    toastr.error('El Idioma ya estaba agregado');
                    return;
                }
            }

            // verificar datos cada uno
            var titulo = document.getElementById('titulo-nuevo').value;
            var subtitulo = document.getElementById('subtitulo-nuevo').value;

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            // subtitulo y descripcion son opcionales
            if(titulo.length > 100){
                toastr.error('Título 100 caracteres máximo')
                return;
            }

            if(subtitulo.length > 200){
                toastr.error('Subtitulo 200 caracteres máximo')
                return;
            }


            let formData = new FormData();
            formData.append('ididioma', idIdiomaSelect);
            formData.append('titulo',titulo);
            formData.append('subtitulo', subtitulo);

            openLoading();

            axios.post('/admin/comparteapp/registrar/idioma', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        Swal.fire({
                            title: "Idioma Agregado",
                            text: "",
                            icon: 'success',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
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
