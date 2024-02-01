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
                    <h3 class="card-title">Crear Nuevo Devocional</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">

                            <section>
                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Fecha:</label>
                                            <input type="date" class="form-control" id="fecha" value="">
                                        </div>
                                    </div>
                                </div>

                            </section>

                            <section style="margin-top: 15px">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label class="control-label">Imagen (Ejemplo: 400x400 px)</label>
                                            <input type="file" class="form-control" style="color:#191818" id="imagen-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group">
                                        <div class="form-group">
                                            <label class="control-label">Imagen Portada (Ejemplo: 600x400 px)</label>
                                            <input type="file" class="form-control" style="color:#191818" id="imagenportada-nuevo" accept="image/jpeg, image/jpg, image/png"/>
                                        </div>
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
                                <label class="control-label">Idioma para nuevo Devocional:</label>
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
                        <th style="width: 3%">#</th>
                        <th style="width: 10%">Idioma</th>
                        <th style="width: 6%">Título</th>
                        <th style="width: 6%">Subtitulo (Opcional)</th>
                        <th style="width: 6%">Descripción (Opcional)</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <div class="modal-footer justify-content-between float-right" style="margin-top: 25px; margin-bottom: 30px;">
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Devocional</button>
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
                                    <input type="text" maxlength="150" autocomplete="off" class="form-control" id="titulo-plan">
                                </div>

                                <div class="form-group">
                                    <label>Subtitulo (Opcional)</label>
                                    <input type="text" maxlength="50" autocomplete="off" class="form-control" id="subtitulo-plan">
                                </div>

                                <div class="form-group">
                                    <label>Descripción (Opcional)</label>
                                    <textarea name="editor-descripcion" id="editor-descripcion"></textarea>
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
    <script src="{{ asset('js/ckeditor5.js') }}"></script>


    <script type="text/javascript">
        $(document).ready(function() {

            ClassicEditor
                .create( document.querySelector( '#editor-descripcion' ), {

                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'underline',
                            'strikethrough',
                            '|',
                            'numberedList',
                            'bulletedList',
                            '|',
                            'alignment',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'es',
                })
                .then( editor => {

                } )
                .catch( error => {
                } );

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>


        function verificarIdiomaTabla(){

            var idIdiomaSelect = document.getElementById('select-idioma').value;

            var arrayIdIdioma = $("input[name='arrayNombre[]']").map(function(){return $(this).attr("data-ididioma");}).get();


            for(var a = 0; a < arrayIdIdioma.length; a++){

                let infoIdMedic = arrayIdIdioma[a];

                if(idIdiomaSelect == infoIdMedic){
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

            var arrayIdIdioma = $("input[name='arrayNombre[]']").map(function(){return $(this).attr("data-ididioma");}).get();

            for(var a = 0; a < arrayIdIdioma.length; a++){

                let infoIdMedic = arrayIdIdioma[a];

                if(idIdiomaSelect == infoIdMedic){
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
            if(subtitulo.length > 50){
                toastr.error('Subtitulo 50 caracteres máximo')
                return;
            }

            const editorData = .getData();


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
                "<input name='arrayTitulo[]' disabled value='" + titulo + "' class='form-control' type='text'>" +
                "</td>" +



                "<td>" +
                "<textarea name='arrayIndicacion[]'  class='form-control' type='text'>" + indicacionesTexto +"</textarea>" +
                "</td>" +


                "<td>" +
                "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
                "</td>" +

                "</tr>";

            $("#matriz tbody").append(markup);


            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            })

        }












        function vistaAtras(){

            window.location.href="{{ url('/admin/historial/clinico/vista') }}/" + idconsulta;
        }



        function nuevoDiagnosticoExtra(){
            document.getElementById("formulario-extradiagnostico").reset();
            $('#modalExtraDiagnostico').modal('show');
        }


        function nuevaViaExtra(){
            document.getElementById("formulario-nuevavia").reset();
            $('#modalExtraVia').modal('show');
        }


        function guardarExtraDiagnostico(){

            var nombre = document.getElementById('extranombre-diagnostico-nuevo').value;
            var descripcion = document.getElementById('extradescripcion-diagnostico-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            openLoading();
            var formData = new FormData();

            formData.append('nombre', nombre);
            formData.append('descripcion', descripcion);

            axios.post(url+'/diagnosticos/guardar/getlistado/completo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Guardado correctamente');

                        document.getElementById("select-diagnostico").options.length = 0;

                        $.each(response.data.lista, function( key, val ){
                            $('#select-diagnostico').append('<option value="' +val.id +'">'+val.nombre+'</option>');
                        });
                        $("#select-diagnostico").trigger("change");

                        $('#modalExtraDiagnostico').modal('hide');
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


        function guardarExtraVia(){

            var nombre = document.getElementById('extranombre-via-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            openLoading();
            var formData = new FormData();

            formData.append('nombre', nombre);

            axios.post(url+'/vias/guardar/getlistado/completo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Guardado correctamente');

                        document.getElementById("select-via").options.length = 0;

                        $.each(response.data.lista, function( key, val ){
                            $('#select-via').append('<option value="' +val.id +'">'+val.nombre+'</option>');
                        });
                        $("#select-via").trigger("change");

                        $('#modalExtraVia').modal('hide');
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



        function cargarTablaProducto(){

            var idFuente = document.getElementById('select-fuente').value;

            document.getElementById("nombre-generico").value = "";

            if(idFuente === ''){
                document.getElementById("select-medicamento").options.length = 0;
                $('#select-medicamento').append('<option value="" selected disabled>Seleccionar Fuente Financiamiento</option>');
            }else{
                openLoading();
                let formData = new FormData();

                formData.append('idfuente', idFuente);

                axios.post(url+'/recetas/medicamentos/porfuente', formData, {
                })
                    .then((response) => {
                        closeLoading();
                        if(response.data.success === 1){

                            document.getElementById("select-medicamento").options.length = 0;


                            // SE AGREGA EL ID DE ENTRADA DETALLE

                            if(response.data.hayfilas){
                                $('#select-medicamento').append('<option value="" data-lote="" data-cantitotal="" data-nombre="" selected>Seleccionar Medicamento</option>');
                                $.each(response.data.dataArray, function( key, val ){
                                    $('#select-medicamento').append('<option value="' +val.id +'" data-lote="' +val.lote +'" data-cantitotal="' +val.cantidadTotal +'" data-nombre="' +val.nombre +'">'+val.nombretotal+'</option>');
                                });
                            }else{
                                $('#select-medicamento').append('<option value="" data-lote="" data-cantitotal="" data-nombre="">Sin Medicamentos</option>');
                            }

                        }else{
                            toastr.error('Información no encontrada');
                        }

                    })
                    .catch((error) => {
                        closeLoading();
                        toastr.error('Información no encontrada');
                    });
            }
        }


        function getNombreGenerico(){

            var miSelect = document.getElementById("select-medicamento");
            var opcionSeleccionada = miSelect.options[miSelect.selectedIndex];
            var dataInfoValue = opcionSeleccionada.getAttribute("data-generico");
            document.getElementById("nombre-generico").value = dataInfoValue;
        }


        function agregarFila(){

            let idmedicamento = document.getElementById("select-medicamento").value;
            let indicacionesTexto = document.getElementById("indicacion-medicamento").value;
            let cantidadSalida = document.getElementById("cantidad").value;

            let idvia = document.getElementById("select-via").value;

            if(idmedicamento === ''){
                toastr.error('Medicamento es requerido');
                return;
            }

            if(indicacionesTexto === ''){
                toastr.error('Indicaciones para Medicamento es requerido');
                return;
            }

            var reglaNumeroEntero = /^[0-9]\d*$/;

            if(cantidadSalida === ''){
                toastr.error('Cantidad a Retirar es requerido');
                return;
            }

            if(!cantidadSalida.match(reglaNumeroEntero)) {
                toastr.error('Cantidad a Retirar es requerido');
                return;
            }

            if(cantidadSalida < 0){
                toastr.error('Cantidad a Retirar no debe tener negativos');
                return;
            }

            if(cantidadSalida > 9000000){
                toastr.error('Cantidad a Retirar máximo debe ser 9 millones');
                return;
            }


            if(idvia === ''){
                toastr.error('Vía es requerida')
                return;
            }


            // VERIFICAR MAXIMO A RETIRAR
            var miSelect = document.getElementById("select-medicamento");
            var opcionSeleccionada = miSelect.options[miSelect.selectedIndex];
            var dataInfoCantidad = opcionSeleccionada.getAttribute("data-cantitotal");
            var dataInfoNombre = opcionSeleccionada.getAttribute("data-nombre");
            var dataInfoLote = opcionSeleccionada.getAttribute("data-lote");

            let totalHay = parseInt(dataInfoCantidad);
            let totalSalida = parseInt(cantidadSalida);

            if(totalSalida > totalHay){
                Swal.fire({
                    title: 'Cantidad Excedida',
                    text: 'Actualmente hay Disponible: ' + totalHay,
                    icon: 'info',
                    showCancelButton: false,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    if (result.isConfirmed) {

                    }
                })

                return;
            }


            // INGRESAR A TABLA


            var nFilas = $('#matriz >tbody >tr').length;
            nFilas += 1;

            var markup = "<tr>" +

                "<td>" +
                "<p id='fila" + (nFilas) + "' class='form-control' style='max-width: 65px'>" + (nFilas) + "</p>" +
                "</td>" +

                "<td>" +
                "<input name='arrayNombre[]' disabled data-idmedicamento='" + idmedicamento + "' value='" + dataInfoNombre + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input disabled value='" + dataInfoLote + "' class='form-control' type='text'>" +
                "</td>" +

                "<td>" +
                "<input name='arrayCantidad[]' disabled value='" + cantidadSalida + "' class='form-control' type='number'>" +
                "<input name='arrayVia[]' data-idvia='" + idvia + "' disabled type='hidden'>" +
                "</td>" +

                "<td>" +
                "<textarea name='arrayIndicacion[]'  class='form-control' type='text'>" + indicacionesTexto +"</textarea>" +
                "</td>" +


                "<td>" +
                "<button type='button' class='btn btn-block btn-danger' onclick='borrarFila(this)'>Borrar</button>" +
                "</td>" +

                "</tr>";

            $("#matriz tbody").append(markup);


            Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            })

            document.getElementById("indicacion-medicamento").value = "";
            document.getElementById("cantidad").value = "";
            document.getElementById("bloqueGuardarTabla").style.display = "block";

            document.getElementById('select-via').selectedIndex = 0;
            $("#select-via").trigger("change");
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

            if(conteo == 0){
                document.getElementById("bloqueGuardarTabla").style.display = "none";
            }
        }


        function preguntarGuardar(){

            Swal.fire({
                title: '¿Guardar Receta?',
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
                    registrarMedicamento();
                }
            })
        }


        function registrarMedicamento(){

            var fecha = document.getElementById('fecha').value;
            var diagnostico = document.getElementById('select-diagnostico').value;
            var indicacionGeneral = document.getElementById('text-indicacion-general').value;
            var proximaCita = document.getElementById('proxima-cita').value;


            if(fecha === ''){
                toastr.error('Fecha es requerido');
                return;
            }

            if(diagnostico === ''){
                toastr.error('Diagnóstico es requerido');
                return;
            }

            var nRegistro = $('#matriz > tbody >tr').length;

            if (nRegistro <= 0){
                toastr.error('Lista de Medicamentos son requeridos');
                return;
            }


            var arrayIdMedicamentos = $("input[name='arrayNombre[]']").map(function(){return $(this).attr("data-idmedicamento");}).get();
            var arrayIdVia = $("input[name='arrayVia[]']").map(function(){return $(this).attr("data-idvia");}).get();

            var arrayCantidad = $("input[name='arrayCantidad[]']").map(function(){return $(this).val();}).get();
            var arrayDeTextareas = $("#matriz textarea[name='arrayIndicacion[]']").map(function(){
                return $(this).val();
            }).get();


            var reglaNumeroEntero = /^[0-9]\d*$/;

            // VALIDACIONES DE CADA FILA, RECORRER 1 ELEMENTO YA QUE TODOS TIENEN LA MISMA CANTIDAD DE FILAS

            colorBlancoTabla();

            for(var a = 0; a < arrayIdMedicamentos.length; a++){

                let infoIdMedic = arrayIdMedicamentos[a];
                let infoCantidad = arrayCantidad[a];
                let infoIndicaciones = arrayDeTextareas[a];

                if(infoIdMedic == ''){
                    colorRojoTabla(a);
                    alertaMensaje('info', 'No encontrado', 'En la Fila #' + (a+1) + " El Medicamento no se encuentra. Por favor borrar la Fila y agregar de nuevo el Medicamento");
                    return;
                }

                // **** VALIDAR CANTIDAD DE PRODUCTO

                if (infoCantidad === '') {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad de Medicamento es requerida. Por favor borrar la Fila y buscar de nuevo el Medicamento');
                    return;
                }

                if (!infoCantidad.match(reglaNumeroEntero)) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad debe ser entero y no negativo. Por favor borrar la Fila y buscar de nuevo el Medicamento');
                    return;
                }

                if (infoCantidad <= 0) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad no debe ser negativo. Por favor borrar la Fila y buscar de nuevo el Medicamento');
                    return;
                }

                if (infoCantidad > 9000000) {
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Cantidad máximo 9 millones. Por favor borrar la Fila y buscar de nuevo el Medicamento');
                    return;
                }


                if(infoIndicaciones === ''){
                    colorRojoTabla(a);
                    toastr.error('Fila #' + (a + 1) + ' Descripción para el Medicamento es requerido');
                    return;
                }
            }

            openLoading();


            let formData = new FormData();

            const contenedorArray = [];


            for(var i = 0; i < arrayIdMedicamentos.length; i++){

                let infoIdMedicamento = arrayIdMedicamentos[i];
                let infoCantidad = arrayCantidad[i];
                let infoIndicacion = arrayDeTextareas[i];
                let infoIdVia = arrayIdVia[i];

                // ESTOS NOMBRES SE UTILIZAN EN CONTROLADOR
                contenedorArray.push({ infoIdMedicamento, infoCantidad, infoIndicacion, infoIdVia});
            }

            formData.append('contenedorArray', JSON.stringify(contenedorArray));
            formData.append('idconsulta', idconsulta);
            formData.append('fecha', fecha);
            formData.append('diagnostico', diagnostico);
            formData.append('indicacionGeneral', indicacionGeneral);
            formData.append('proximaCita', proximaCita);


            axios.post(url+'/recetas/registro/parapaciente', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        Swal.fire({
                            title: "Receta Ya Registrada",
                            text: "Para esta consulta ya se ha registrado una Receta",
                            icon: 'error',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                salirVistaHistorialClinico();
                            }
                        })

                    }
                    else if(response.data.success === 2){
                        Swal.fire({
                            title: "Receta Registrada",
                            text: "",
                            icon: 'success',
                            showCancelButton: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                salirVistaHistorialClinico();
                            }
                        })
                    }
                    else{
                        toastr.error('error al guardar');
                    }
                })
                .catch((error) => {
                    toastr.error('error al guardar');
                    closeLoading();
                });
        }

        function colorBlancoTabla(){
            $("#matriz tbody tr").css('background', 'white');
        }

        function colorRojoTabla(index){
            $("#matriz tr:eq("+(index+1)+")").css('background', '#F1948A');
        }


        function salirVistaHistorialClinico(){


            window.location.href="{{ url('/admin/historial/clinico/vista') }}/" + idconsulta;
        }



    </script>


@endsection
