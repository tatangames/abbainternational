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
                    <h3 class="card-title">Editar Tipo Insignia</h3>
                </div>
                <div class="card-body">
                    <div>
                        <div class="col-md-12">

                            <section style="margin-top: 15px">

                                <label class="control-label">Imagen (Ejemplo: 400x400 px)</label>
                                <div class="row">
                                    <div class="input-group input-group col-md-4">
                                        <input type="file" class="form-control" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>

                                        <span class="input-group-append">
                                        <button type="button" class="btn btn-info btn-sm" onclick="actualizarImagen()">Actualizar</button>
                                            </span>
                                    </div>
                                </div>




                            </section>


                            <hr><br>

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
                                <label class="control-label">Idioma:</label>
                                <select class="form-control" id="select-idioma">
                                    @foreach($arrayIdiomas as $item)
                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick="verificarIdiomaTabla()">Agregar Idioma</button>
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
                        <th style="width: 10%">Subtitulo (Opcional)</th>
                        <th style="width: 6%">Opciones</th>
                    </tr>
                    </thead>
                    <tbody>


                    @foreach($arrayInsigniaTexto as $item)
                        <tr>
                            <td>
                                <p id="fila" class="form-control" style="max-width: 65px">{{ $item->contador }}</p>
                            </td>

                            <td>
                                <!-- data-ididioma se utiliza para comparar si falta agregar idioma nuevo -->
                                <input name="arrayIdioma[]" disabled data-idinsigniatexto="{{ $item->id }}" data-ididioma="{{ $item->id_idioma_planes }}" value="{{ $item->idioma }}" class="form-control" type="text">
                            </td>

                            <td>
                                <input name="arrayTitulo[]" disabled value="{{ $item->texto_1 }}" class="form-control" type="text">
                            </td>

                            <td>
                                <input name="arraySubtitulo[]" disabled value="{{ $item->texto_2 }}" class="form-control" type="text">
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
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Actualizar</button>
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
                                    <label>Título</label>
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="titulo-plan">
                                </div>

                                <div class="form-group">
                                    <label>Descripción (Opcional)</label>
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="subtitulo-plan">
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="AgregarNuevoIdioma()">Guardar</button>
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
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="titulo-plan-editado">
                                </div>

                                <div class="form-group">
                                    <label>Descripción (Opcional)</label>
                                    <input type="text" maxlength="200" autocomplete="off" class="form-control" id="subtitulo-plan-editado">
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


    <script type="text/javascript">
        $(document).ready(function() {



            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        // VARIABLES PARA EDITAR CADA FILA, REFERENCIAS
        var referenciaArrayTitulo;
        var referenciaArraySubtitulo;

        var referenciaArrayTituloNoti;
        var referenciaArrayDesNoti;

        // obtener los datos de la fila y llevarlos al modal
        function editarFila(e){

            var fila = $(e).closest('tr');

            var valorInputTitulo = fila.find('input[name="arrayTitulo[]"]').val();
            var valorInputTituloRef = fila.find('input[name="arrayTitulo[]"]');
            referenciaArrayTitulo = valorInputTituloRef;

            var valorInputSubtitulo = fila.find('input[name="arraySubtitulo[]"]').val();
            var valorInputSubtituloRef = fila.find('input[name="arraySubtitulo[]"]');
            referenciaArraySubtitulo = valorInputSubtituloRef;



            var valorInputTituloNoti = fila.find('input[name="arrayTiNoti[]"]').val();
            var valorInputTituloRefNoti = fila.find('input[name="arrayTiNoti[]"]');
            referenciaArrayTituloNoti = valorInputTituloRefNoti;


            var valorInputDesNoti = fila.find('input[name="arrayDesNoti[]"]').val();
            var valorInputDesNotiRedf = fila.find('input[name="arrayDesNoti[]"]');
            referenciaArrayDesNoti = valorInputDesNotiRedf;


            // limpiar modal
            document.getElementById("formulario-datoseditados").reset();

            $('#titulo-plan-editado').val(valorInputTitulo);
            $('#subtitulo-plan-editado').val(valorInputSubtitulo);

            $('#titulo-notificacion-editado').val(valorInputTituloNoti);
            $('#descripcion-notificacion-editado').val(valorInputDesNoti);

            $('#modalDatosEditados').modal('show');
        }

        // METER LOS DATOS DE NUEVO A LA FILA
        function actualizarDatosEditados(){

            var titulo = document.getElementById('titulo-plan-editado').value;
            var subtitulo = document.getElementById('subtitulo-plan-editado').value; // opcional



            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            // subtitulo y descripcion son opcionales
            if(subtitulo.length > 200){
                toastr.error('Descripción 200 caracteres máximo')
                return;
            }


            // Actualizar la fila con las referencias
            referenciaArrayTitulo.val(titulo);
            referenciaArraySubtitulo.val(subtitulo);

            $('#modalDatosEditados').modal('hide');
        }


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


        // AGREGAR NUEVO IDIOMA A LA FILAS PARA DESPUES ACTUALIZAR
        function AgregarNuevoIdioma(){

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
            var titulo = document.getElementById('titulo-plan').value;
            var subtitulo = document.getElementById('subtitulo-plan').value; // opcional

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            // subtitulo y descripcion son opcionales
            if(subtitulo.length > 200){
                toastr.error('Descripción 200 caracteres máximo')
                return;
            }

            // es nuevo idioma
            let valorNull = 0;


            // AGREGAR A FILA

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayIdioma[]' disabled data-idinsigniatexto='" + valorNull + "'  data-ididioma='" + idIdiomaSelect + "' value='" + selectedOptionText + "' class='form-control' type='text'>" +
                "</td>" +


                "<td>" +
                "<input name='arrayTitulo[]' disabled value='" + titulo + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='arraySubtitulo[]' disabled value='" + subtitulo + "' class='form-control' type='text'>" +
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



        function vistaAtrasPlanes(){
            window.location.href="{{ url('/admin/planes/index') }}";
        }

        function preguntarGuardar(){

            Swal.fire({
                title: '¿Actualizar?',
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
                    actualizarInsigniaFinal();
                }
            })
        }

        // Actualizando
        function actualizarInsigniaFinal(){

            var selectIdioma = document.getElementById("select-idioma");

            // Verificar que haya ingresado todos los idiomas
            let conteoIdioma = selectIdioma.length;

            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro !== conteoIdioma){
                toastr.error('Idiomas son requeridos');
                return;
            }


            let idtipoinsignia = {{ $idtipoinsignia }};

            // obtener ID idioma, titulo, subtitulo

            let formData = new FormData();
            const contenedorArray = [];
            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();
            var arrayIdInsigniaTexto = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-idinsigniatexto");}).get();
            var arrayTitulo = $("input[name='arrayTitulo[]']").map(function(){return $(this).val();}).get();
            var arraySubtitulo = $("input[name='arraySubtitulo[]']").map(function(){return $(this).val();}).get();


            for(var i = 0; i < arrayIdIdioma.length; i++){
                let infoIdInsigniaTexto = arrayIdInsigniaTexto[i];
                let infoIdIdioma = arrayIdIdioma[i];
                let infoTitulo = arrayTitulo[i];
                let infoSubtitulo = arraySubtitulo[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdInsigniaTexto, infoIdIdioma, infoTitulo, infoSubtitulo});
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('idtipoinsignia', idtipoinsignia);

            openLoading();

            axios.post('/admin/tipoinsignias/actualizar/datos', formData, {
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


        // actualizar solo imagen
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

            let idtipoinsignia = {{ $idtipoinsignia }};

            let formData = new FormData();
            formData.append('idtipoinsignia', idtipoinsignia);
            formData.append('imagen', imagen.files[0]);

            openLoading();

            axios.post('/admin/tipoinsignias/actualizar/imagen', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        document.getElementById('imagen-nuevo').value = "";
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
