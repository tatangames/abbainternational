@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />

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



    <section class="content-header">
        <div class="row mb-12">
            <div class="col-sm-12">
                <button type="button" class="btn btn-info btn-sm" onclick="crearPreguntasPorDefecto()">Crear Preguntas por Defecto</button>
            </div>
        </div>
    </section>



    <section class="content-header">
        <div class="row mb-12">
            <div class="col-sm-12">
                <section>

                    <div class="form-group col-md-3">
                        <label class="control-label">Seleccionar Imagen</label>
                        <select class="form-control" id="select-imagen">
                            @foreach($arrayImagenes as $item)
                                <option value="{{$item->id}}">{{$item->nombre}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3" style="margin-top: 35px">
                        <label class="control-label">Idioma</label>
                        <select class="form-control" id="select-idioma">
                            @foreach($arrayIdiomas as $item)
                                <option value="{{$item->id}}">{{$item->nombre}}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="btn btn-info btn-sm" onclick="verificarTextoTabla()">Agregar Texto</button>

                </section>
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

                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <div class="modal-footer justify-content-between float-right" style="margin-top: 25px; margin-bottom: 30px;">
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Preguntas</button>
    </div>




    <!-- MODAL PARA AGREGAR DATOS -->

    <div class="modal fade" id="modalDatos" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Información</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-datos">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Descripción </label>
                                    <textarea name="content" id="editor"></textarea>
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

            ClassicEditor
                .create(document.querySelector('#editor'), {
                    language: 'es',
                })
                .then(editor => {

                    varGlobalEditorNuevo = editor;
                })
                .catch(error => {

                });

            $("#toggle-requerida").prop("checked", true);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        // verificar si idioma no esta agregado a tabla, antes de abrir modal
        function verificarTextoTabla(){

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
            document.getElementById("formulario-datos").reset();
            varGlobalEditorNuevo.setData("");

            $('#modalDatos').modal('show');
        }

        // agregar dato nuevo a fila
        function AgregarFila(){

            // verificar siempre
            var idIdiomaSelect = document.getElementById('select-idioma').value;

            let selectElement = document.getElementById("select-idioma");
            let selectedIndex = selectElement.selectedIndex;
            let selectedOptionText = selectElement.options[selectedIndex].text;

            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();

            for(var a = 0; a < arrayIdIdioma.length; a++){

                let infoIdLenguaje = arrayIdIdioma[a];

                if(idIdiomaSelect == infoIdLenguaje){
                    toastr.error('El Idioma ya estaba agregado');
                    return;
                }
            }


            // PUEDE SER NULL
            const txtdescripcion = varGlobalEditorNuevo.getData();


            // AGREGAR A FILA

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayIdioma[]' disabled data-ididioma='" + idIdiomaSelect + "' value='" + selectedOptionText + "' class='form-control' type='text'>" +
                "<textarea name='arrayDescripcion[]' style='display: none' class='form-control'>" + txtdescripcion + "</textarea>" +
                "</td>" +

                "<td>" +
                "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
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

            $('#modalDatos').modal('hide');
        }



        function preguntarGuardar(){

            Swal.fire({
                title: '¿Guardar Pregunta?',
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
                    guardarBloquePregunta();
                }
            })
        }

        function guardarBloquePregunta(){

            var selectIdioma = document.getElementById("select-idioma");
            var selectImagen = document.getElementById("select-imagen").value;

            // Verificar que haya ingresado todos los idiomas
            let conteoIdioma = selectIdioma.length;


            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro !== conteoIdioma){
                toastr.error('Idiomas son requeridos');
                return;
            }

            let idplanbloquedetalle = {{ $idplanbloquedetalle }};


            let formData = new FormData();
            const contenedorArray = [];
            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();

            // texto de pregunta que puede ser null
            var arrayDescripcion = $("textarea[name='arrayDescripcion[]']").map(function(){return $(this).val();}).get();


            for(var i = 0; i < arrayIdIdioma.length; i++){

                let infoIdIdioma = arrayIdIdioma[i];
                let infoDescripcion = arrayDescripcion[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdIdioma, infoDescripcion });
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('idplanbloquedetalle', idplanbloquedetalle);
            formData.append('idimagen', selectImagen);

            openLoading();

            axios.post('/admin/preguntas/registrar/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        Swal.fire({
                            title: "Pregunta Creada",

                            icon: 'success',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                limpiarParaNuevoDato();
                            }
                        })
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

        function limpiarParaNuevoDato(){
            var tbody = document.getElementById('matriz').querySelector('tbody');
            tbody.innerHTML = '';
        }

        function borrarFila(elemento){
            var tabla = elemento.parentNode.parentNode;
            tabla.parentNode.removeChild(tabla);
            setearFila();
        }

        function setearFila(){

            var table = document.getElementById('matriz');
            var conteo = 0;
            for (var r = 1, n = table.rows.length; r < n; r++) {
                conteo +=1;
                var element = table.rows[r].cells[0].children[0];
                document.getElementById(element.id).innerHTML = ""+conteo;
            }
        }


        function crearPreguntasPorDefecto(){

            let idplanbloquedetalle = {{ $idplanbloquedetalle }};


            let formData = new FormData();
            formData.append('idplanbloquedetalle', idplanbloquedetalle);

            openLoading();

            axios.post('/admin/preguntas/registrar/nuevodefecto', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        Swal.fire({
                            title: "No creado",
                            text: "Ya hay al menos 1 Pregunta registrada",
                            icon: 'info',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                               limpiarParaNuevoDato()
                            }
                        })
                    }
                    else if(response.data.success === 2){
                        Swal.fire({
                            title: "Preguntas Por Defecto Creada",

                            icon: 'success',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                volverAtras();
                            }
                        })
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

        function volverAtras(){
            history.go(-1);
        }


    </script>


@endsection
