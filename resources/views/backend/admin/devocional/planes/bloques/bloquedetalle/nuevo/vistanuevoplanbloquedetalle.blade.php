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


    <section class="content" style="margin-top: 20px">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Crear Detalle para el Bloque Devocional</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">



                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="content-header">
        <div class="row mb-12">
            <div class="col-sm-12">
                <section>
                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Idioma para Bloque Devocional:</label>
                                <select class="form-control" id="select-idioma">
                                    @foreach($arrayIdiomas as $item)
                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick="verificarTextoTabla()">Agregar Texto Personalizado</button>


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
                        <th style="width: 10%">Título</th>
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
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Detalle</button>
    </div>




    <!-- MODAL PARA AGREGAR DATOS DE UN TEXTO PERSONALIZADO -->

    <div class="modal fade" id="modalDatosPersonalizado" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Información</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-personalizado">
                        <div class="card-body">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Título</label>
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="titulo-nuevo">
                                </div>

                                <div class="form-group">
                                    <label>Descripción </label>
                                    <i class="far fa-question-circle" onclick="queEsEsto()"></i>
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

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>


        function queEsEsto(){
            Swal.fire({
                title: "Descripción",
                text: "Esto va arriba de las preguntas",
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
            document.getElementById("formulario-personalizado").reset();
            varGlobalEditorNuevo.setData("");

            $('#modalDatosPersonalizado').modal('show');
        }


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

            // verificar datos cada uno
            var titulo = document.getElementById('titulo-nuevo').value; // 100

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            // subtitulo y descripcion son opcionales
            if(titulo.length > 100){
                toastr.error('Título 100 caracteres máximo')
                return;
            }

            const editorDataDescripcion = varGlobalEditorNuevo.getData();

            // AGREGAR A FILA

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayIdioma[]' disabled data-ididioma='" + idIdiomaSelect + "' value='" + selectedOptionText + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='arrayTitulo[]' disabled   value='" + titulo + "' class='form-control' type='text'>" +
                "<textarea name='arrayDescripcion[]' style='display: none' class='form-control'>" + editorDataDescripcion + "</textarea>" +

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

            $('#modalDatosPersonalizado').modal('hide');
        }


        function vistaAtrasPlanesBloques(){
            let idplanbloque = {{ $idplanbloque }};
            window.location.href="{{ url('/admin/planbloquedetalle/vista') }}/" + idplanbloque;
        }

        function preguntarGuardar(){

            Swal.fire({
                title: '¿Guardar Detalle?',
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
                    guardarBloqueFinal();
                }
            })
        }

        function guardarBloqueFinal(){

            var selectIdioma = document.getElementById("select-idioma");

            // Verificar que haya ingresado todos los idiomas
            let conteoIdioma = selectIdioma.length;


            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro !== conteoIdioma){
                toastr.error('Idiomas son requeridos');
                return;
            }


            let idplanbloque = {{ $idplanbloque }};

            let formData = new FormData();
            const contenedorArray = [];
            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();
            var arrayTitulo = $("input[name='arrayTitulo[]']").map(function(){return $(this).val();}).get();
            var arrayDescripcion = $("textarea[name='arrayDescripcion[]']").map(function(){return $(this).val();}).get();

            for(var i = 0; i < arrayIdIdioma.length; i++){

                let infoIdIdioma = arrayIdIdioma[i];
                let infoTitulo = arrayTitulo[i];
                let infoDescripcion = arrayDescripcion[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdIdioma, infoTitulo, infoDescripcion });
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('idplanbloque', idplanbloque);

            openLoading();

            axios.post('/admin/planbloquedetalle/agregar/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        Swal.fire({
                            title: "Detalle Creado",
                            text: "Se deberan registrar el devocional y sus preguntas",
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


    </script>


@endsection
