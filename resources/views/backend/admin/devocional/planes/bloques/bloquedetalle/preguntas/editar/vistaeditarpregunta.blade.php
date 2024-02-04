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
                    <h3 class="card-title">Editar Pregunta</h3>
                </div>
                <div class="card-body">
                    <div class="col-md-12">

                        <section>
                            <div class="form-group col-md-3">
                                <label class="control-label">Seleccionar Imagen</label>
                                <select class="form-control" id="select-imagen">
                                    @foreach($arrayImagenes as $item)
                                        @if($infoPregunta->id_imagen_pregunta == $item->id)
                                            <option value="{{$item->id}}" selected>{{$item->nombre}}</option>
                                        @else
                                            <option value="{{$item->id}}">{{$item->nombre}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="margin-left: 5px">
                                <label>Pregunta es Requerida</label><br>
                                <label class="switch" style="margin-top:10px">
                                    <input type="checkbox" id="toggle-requerida">
                                    <div class="slider round">
                                        <span class="on">Sí</span>
                                        <span class="off">No</span>
                                    </div>
                                </label>
                            </div>


                            <div class="form-group col-md-3" style="margin-top: 35px">
                                <label class="control-label">Idioma</label>
                                <select class="form-control" id="select-idioma">
                                    @foreach($arrayIdiomas as $item)
                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>



                            <button type="button" class="btn btn-info btn-sm" onclick="verificarIdiomaTabla()">Agregar Idioma</button>


                        </section>



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
                        <th style="width: 6%">Opciones</th>
                    </tr>
                    </thead>
                    <tbody>


                    @foreach($arrayBloquePreguntasTextos as $item)
                        <tr>
                            <td>
                                <p id="fila" class="form-control" style="max-width: 65px">{{ $item->contador }}</p>
                            </td>

                            <td>
                                <!-- data-ididioma se utiliza para comparar si falta agregar idioma nuevo -->
                                <input name="arrayIdioma[]" disabled data-idbloquepreguntastextos="{{ $item->id }}" data-ididioma="{{ $item->id_idioma_planes }}" value="{{ $item->idioma }}" class="form-control" type="text">

                                <textarea name="arrayDescripcion[]" disabled style="display: none" class="form-control">{{ $item->texto }}</textarea>

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


    <div class="modal-footer justify-content-between float-right" style="margin-top: 25px; margin-bottom: 30px;">
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Actualizar Pregunta</button>
    </div>


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
                                    <label>Descripción </label>
                                    <div id="editor-nuevo"></div>
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
                                    <label>Descripción </label>
                                    <div id="editor-editar"></div>
                                </div>


                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="actualizarDatosEditados()">Actualizar Preguntas</button>
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

            window.varGlobalEditorNuevo;
            window.varGlobalEditorEditar;

            ClassicEditor
                .create(document.querySelector('#editor-nuevo'), {
                    language: 'es',
                })
                .then(editor => {
                    varGlobalEditorNuevo = editor;
                })
                .catch(error => {

                });

            ClassicEditor
                .create(document.querySelector('#editor-editar'), {
                    language: 'es',
                })
                .then(editor => {
                    varGlobalEditorEditar = editor;
                })
                .catch(error => {

                });


            let valor = {{ $infoPregunta->requerido }};

            if(valor == 1){
                $("#toggle-requerida").prop("checked", true);
            }

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        var referenciaArrayDescripcion;

        // obtener los datos de la fila y llevarlos al modal
        function editarFila(e){

            var fila = $(e).closest('tr');

            var valorInputDescripcion = fila.find('textarea[name="arrayDescripcion[]"]').val();
            var valorInputDescripcionRef = fila.find('textarea[name="arrayDescripcion[]"]');
            referenciaArrayDescripcion = valorInputDescripcionRef;

            varGlobalEditorEditar.setData(valorInputDescripcion);

            $('#modalDatosEditados').modal('show');
        }

        // METER LOS DATOS DE NUEVO A LA FILA
        function actualizarDatosEditados(){

            const editorDataDescripcionEdit = varGlobalEditorEditar.getData();
            referenciaArrayDescripcion.val(editorDataDescripcionEdit);

            $('#modalDatosEditados').modal('hide');
        }


        // verificar que idioma no este en la tabla, para abrir modal y agregar el nuevo idioma a la fila
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


            // AGREGAR A FILA
            const editorDataDescripcionEdit = varGlobalEditorNuevo.getData();

            // COMO ES NUEVA FILA, SE IDENTIFICARA CON 0, PARA CREAR EL REGISTRO
            let valorNull = 0;

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayIdioma[]' disabled    data-idbloquepreguntastextos='" + valorNull + "'  data-ididioma='" + idIdiomaSelect + "' value='" + selectedOptionText + "' class='form-control' type='text'>" +
                "<textarea name='arrayDescripcion[]' style='display: none' class='form-control'>" + editorDataDescripcionEdit + "</textarea>" +
                "</td>" +

                "<td>" +
                "<button type='button' class='btn btn-block btn-info' onclick='editarFila(this)'>Editar</button>" +
                "</td>" +

                "</tr>";

            $("#matriz tbody").append(markup);


            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Idioma agregado',
                showConfirmButton: false,
                timer: 1500
            })


            $('#modalDatosIdioma').modal('hide');
        }


        function preguntarGuardar(){

            Swal.fire({
                title: '¿Actualizar Pregunta?',
                text: '',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                allowOutsideClick: false,
                confirmButtonText: 'SI',
                cancelButtonText: 'NO'
            }).then((result) => {
                if (result.isConfirmed) {
                    actualizarPreguntaFinal();
                }
            })
        }

        // Actualizando los datos en el servidor
        function actualizarPreguntaFinal(){

            var selectIdioma = document.getElementById("select-idioma");

            // Verificar que haya ingresado todos los idiomas
            let conteoIdioma = selectIdioma.length;


            var nRegistro = $('#matriz > tbody >tr').length;


            if (nRegistro !== conteoIdioma){
                toastr.error('Idiomas son requeridos');
                return;
            }

            // obtener ID idioma, titulo, subtitulo, descripcion

            let idbloquepreguntas = {{ $infoPregunta->id }};

            // id imagen y toggle
            var selectImagen = document.getElementById("select-imagen").value;
            let t = document.getElementById('toggle-requerida').checked;
            let toggle = t ? 1 : 0;


            let formData = new FormData();
            const contenedorArray = [];
            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();
            var arrayIdBloquePreguntasTextos = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-idbloquepreguntastextos");}).get();

            var arrayDescripcion = $("textarea[name='arrayDescripcion[]']").map(function(){return $(this).val();}).get();

            for(var i = 0; i < arrayIdIdioma.length; i++){
                let infoIdBloquePreguntaTextos = arrayIdBloquePreguntasTextos[i];
                let infoIdIdioma = arrayIdIdioma[i];
                let infoDescripcion = arrayDescripcion[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdBloquePreguntaTextos, infoIdIdioma, infoDescripcion});
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('idbloquepreguntas', idbloquepreguntas);
            formData.append('idimagen', selectImagen);
            formData.append('toggle', toggle);

            openLoading();

            axios.post('/admin/preguntas/editar', formData, {
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




    </script>


@endsection
