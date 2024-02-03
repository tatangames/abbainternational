@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
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

    <section class="content-header">
        <div class="container-fluid">
            <button type="button" style="font-weight: bold; background-color: #2339cc; color: white !important;" onclick="vistaAtras()" class="button button-3d button-rounded button-pill button-small">
                <i class="fas fa-arrow-left"></i>
                Atras
            </button>
        </div>
    </section>


    <section class="content" style="margin-top: 20px">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Editar Video URL</h3>
                </div>
                <div class="card-body">
                    <div>
                        <div class="col-md-12">

                            <div class="row">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="control-label">Fecha</label>
                                        <input type="date" class="form-control" id="fecha" value="{{ $infoVideo->fecha }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="max-width: 20%">
                                <label class="control-label">Tipo Video</label>
                                <select class="form-control" id="select-tipovideo">
                                    @foreach($arrayTipo as $item)
                                        @if($infoVideo->id_tipo_video == $item->id)
                                            <option value="{{$item->id}}" selected>{{$item->nombre}}</option>
                                        @else
                                            <option value="{{$item->id}}">{{$item->nombre}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group" style="max-width: 35%">
                                <label class="control-label">URL Video</label>
                                <input type="text" maxlength="100" class="form-control" id="url-nuevo" value="{{ $infoVideo->url_video }}">
                            </div>



                                <label class="control-label">Imagen</label>

                                <div class="row">

                                    <div class="input-group input-group col-md-6">
                                        <input type="file" class="form-control" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>

                                        <span class="input-group-append">
                                            <button type="button" class="btn btn-info btn-sm" onclick="actualizarImagen()">Actualizar</button>
                                            </span>
                                    </div>

                                </div>

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
                        <th style="width: 6%">Opciones</th>
                    </tr>
                    </thead>
                    <tbody>


                    @foreach($arrayVideosTextos as $item)
                        <tr>
                            <td>
                                <p id="fila" class="form-control" style="max-width: 65px">{{ $item->contador }}</p>
                            </td>

                            <td>
                                <!-- data-ididioma se utiliza para comparar si falta agregar idioma nuevo -->
                                <input name="arrayIdioma[]" disabled data-idvideotexto="{{ $item->id }}" data-ididioma="{{ $item->id_idioma_planes }}" value="{{ $item->idioma }}" class="form-control" type="text">
                            </td>

                            <td>
                                <input name="arrayTitulo[]" disabled value="{{ $item->titulo }}" class="form-control" type="text">
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
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="titulo-nuevo">
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
                                    <input type="text" maxlength="100" autocomplete="off" class="form-control" id="titulo-editar">
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

        // obtener los datos de la fila y llevarlos al modal
        function editarFila(e){

            var fila = $(e).closest('tr');

            var valorInputTitulo = fila.find('input[name="arrayTitulo[]"]').val();
            var valorInputTituloRef = fila.find('input[name="arrayTitulo[]"]');
            referenciaArrayTitulo = valorInputTituloRef;

            // limpiar modal
            document.getElementById("formulario-datoseditados").reset();

            $('#titulo-editar').val(valorInputTitulo);

            $('#modalDatosEditados').modal('show');
        }

        // METER LOS DATOS DE NUEVO A LA FILA
        function actualizarDatosEditados(){

            var titulo = document.getElementById('titulo-editar').value;

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            if(titulo.length > 100){
                toastr.error('Título 100 caracteres máximo')
                return;
            }

            // Actualizar la fila con las referencias
            referenciaArrayTitulo.val(titulo);

            $('#modalDatosEditados').modal('hide');
        }



        // abrir modal para nuevo idioma titulo
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
            var titulo = document.getElementById('titulo-nuevo').value;

            if(titulo === ''){
                toastr.error('Título es requerido')
                return;
            }

            if(titulo.length > 100){
                toastr.error('Título 100 caracteres máximo')
                return;
            }

            // AGREGAR A FILA


            // COMO ES NUEVA FILA, SE IDENTIFICARA CON 0, PARA CREAR EL REGISTRO
            let valorNull = 0;

            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayIdioma[]' disabled    data-idvideotexto='" + valorNull + "'  data-ididioma='" + idIdiomaSelect + "' value='" + selectedOptionText + "' class='form-control' type='text'>" +
                "</td>" +


                "<td>" +
                "<input name='arrayTitulo[]' disabled value='" + titulo + "' class='form-control' type='text'>" +
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



        function vistaAtras(){
            window.location.href="{{ url('/admin/videoshoy/vista') }}";
        }

        function preguntarGuardar(){

            Swal.fire({
                title: '¿Actualizar URL?',
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
                    actualizarPlanFinal();
                }
            })
        }

        // Actualizando
        function actualizarPlanFinal(){

            var fecha = document.getElementById('fecha').value;
            var selectIdioma = document.getElementById("select-idioma");
            var selectTipoVideo = document.getElementById('select-tipovideo').value;
            var urlVideo = document.getElementById('url-nuevo').value;

            if(fecha === ''){
                toastr.error('Fecha es Requerida');
                return;
            }

            if(urlVideo === ''){
                toastr.error('URL Video es Requerida');
                return;
            }


            // Verificar que haya ingresado todos los idiomas
            let conteoIdioma = selectIdioma.length;

            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro !== conteoIdioma){
                toastr.error('Idiomas son requeridos');
                return;
            }


            // obtener ID idioma, titulo, subtitulo, descripcion

            let idvideoshoy = {{ $idvideohoy }};

            let formData = new FormData();
            const contenedorArray = [];
            var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();
            var arrayIdVideoTexto = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-idvideotexto");}).get();
            var arrayTitulo = $("input[name='arrayTitulo[]']").map(function(){return $(this).val();}).get();

            for(var i = 0; i < arrayIdIdioma.length; i++){
                let infoIdVideoTexto = arrayIdVideoTexto[i];
                let infoIdIdioma = arrayIdIdioma[i];
                let infoTitulo = arrayTitulo[i];


                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdVideoTexto, infoIdIdioma, infoTitulo});
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('fecha', fecha);
            formData.append('idtipovideo', selectTipoVideo);
            formData.append('urlvideo', urlVideo);
            formData.append('idvideoshoy', idvideoshoy);


            openLoading();

            axios.post('/admin/videoshoy/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    console.log(response);

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



        // actualizar solo imagen para videos hoy
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

            let idvideohoy = {{ $idvideohoy }};

            let formData = new FormData();
            formData.append('idvideohoy', idvideohoy);
            formData.append('imagen', imagen.files[0]);

            openLoading();

            axios.post('/admin/videoshoy/imagen/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        var limpiarInput = document.getElementById('imagen-nuevo');
                        limpiarInput.value = '';
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
