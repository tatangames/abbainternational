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
        <div class="container-fluid">
            <button type="button" style="font-weight: bold; background-color: #2339cc; color: white !important;" onclick="vistaAtrasPlanesBloquesDeta()" class="button button-3d button-rounded button-pill button-small">
                <i class="fas fa-arrow-left"></i>
                Atras
            </button>
        </div>
    </section>

    <section class="content" style="margin-top: 20px">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Texto Devocional</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">

                            <section>
                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">

                                            <div class="form-group">
                                                <label class="control-label">Idioma para Devocional:</label>
                                                <select class="form-control" id="select-idioma">
                                                    @foreach($arrayIdiomas as $item)
                                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <button style="margin-top: 25px" type="button" onclick="modalNuevoTexto()" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus-square"></i>
                                                Nuevo Texto
                                            </button>

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


    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Información Devocional</h3>
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

                    @foreach($arrayCuestionario as $item)
                        <tr>
                            <td>
                                <p id="fila" class="form-control" style="max-width: 65px">{{ $item->contador }}</p>
                            </td>

                            <td>
                                <!-- data-ididioma se utiliza para comparar si falta agregar idioma nuevo -->
                                <input name="arrayIdioma[]" disabled data-idblockcuestionario="{{ $item->id }}" data-ididioma="{{ $item->id_idioma_planes }}" value="{{ $item->idioma }}" class="form-control" type="text">

                                <input name="arrayDescripcion[]" disabled style="display: none" data-txtdescripcion="{{ $item->texto }}" class="form-control" type="hidden">
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
        <button type="button" class="btn btn-success" onclick="preguntarGuardar()">Guardar Detalle</button>
    </div>




    <!-- MODAL PARA AGREGAR DATOS NUEVOS -->

    <div class="modal fade" id="modalNuevoTexto" >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Información</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-nuevo">
                        <div class="card-body">
                            <div class="col-md-12">

                                 <div class="form-group">
                                    <label>Devocional </label>
                                    <textarea name="content" id="editor-nuevo" rows="12" cols="50"></textarea>

                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="AgregarNuevoRegistro()">Guardar</button>
                </div>
            </div>
        </div>
    </div>




    <!-- MODAL PARA EDITAR DATOS  -->

    <div class="modal fade" id="modalEditarTexto" >
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
                                    <label>Devocional </label>
                                    <input id="id-editar" type="hidden">
                                    <textarea name="content" id="editor-editar" rows="12" cols="50"></textarea>

                                </div>

                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="editarRegistro()">Guardar</button>
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

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>


        // abre modal para agregar nuevo texto devocional
        function modalNuevoTexto(){

              var idIdiomaSelect = document.getElementById('select-idioma').value;

              var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();


              for(var a = 0; a < arrayIdIdioma.length; a++){

                  let infoIdLenguaje = arrayIdIdioma[a];

                  if(idIdiomaSelect == infoIdLenguaje){
                      toastr.error('El Idioma ya esta agregado');
                      return;
                  }
              }

              // limpiar modal y ckeditor

              varGlobalEditorNuevo.setData("");
              $('#modalNuevoTexto').css('overflow-y', 'auto');
              $('#modalNuevoTexto').modal({backdrop: 'static', keyboard: false})
          }


        // verificar si se puede agregar con idioma seleccionado
        // al guardarse se actualiza la vista
         function AgregarNuevoRegistro(){
             var idIdiomaSelect = document.getElementById('select-idioma').value;

             var arrayIdIdioma = $("input[name='arrayIdioma[]']").map(function(){return $(this).attr("data-ididioma");}).get();

             for(var a = 0; a < arrayIdIdioma.length; a++){

                 let infoIdLenguaje = arrayIdIdioma[a];

                 if(idIdiomaSelect == infoIdLenguaje){
                     toastr.error('El Idioma ya esta agregado');
                     return;
                 }
             }


             // verificar que lleve texto el ckeditor
             const editorDataDescripcionEdit = varGlobalEditorNuevo.getData();

             if (editorDataDescripcionEdit.trim() === '') {
                 toastr.error("Devocional es requerido");
                 return;
             }


             let idplanbloquedeta = {{ $idplanbloquedetalle }};

             let formData = new FormData();

             formData.append('idblockdetalle', idplanbloquedeta);
             formData.append('ididioma', idIdiomaSelect);
             formData.append('devocional', editorDataDescripcionEdit);

             openLoading();

             axios.post('/admin/planbloquedetalle/guardar/devocional', formData, {
             })
                 .then((response) => {
                     closeLoading();


                     if(response.data.success === 1){
                         Swal.fire({
                             title: "Devocional Agregado",
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
                         toastr.error('Error al registrar');
                     }
                 })
                 .catch((error) => {
                     toastr.error('Error al registrar');
                     closeLoading();
                 });

         }


         // abrir modal para ver los datos que se editaran
        function editarFila(e){
            var fila = $(e).closest('tr');

            var valorInputDescripcionRef = fila.find('input[name="arrayDescripcion[]"]');
            var valorActualDescrip = valorInputDescripcionRef.data('txtdescripcion'); // ESTE ES EL DATA-
            varGlobalEditorEditar.setData(valorActualDescrip);

            // obtener id fila
            var valorArrayIdioma = fila.find('input[name="arrayIdioma[]"]');
            var idblockcuestionario = valorArrayIdioma.data('idblockcuestionario');

            $('#id-editar').val(idblockcuestionario);

            $('#modalEditarTexto').css('overflow-y', 'auto');
            $('#modalEditarTexto').modal({backdrop: 'static', keyboard: false})
        }


        // verificar y actualizar en servidor
        function editarRegistro(){

            var idCuestionarioFila = document.getElementById('id-editar').value;

            const editorDataDescripcionEdit = varGlobalEditorEditar.getData();

            if (editorDataDescripcionEdit.trim() === '') {
                toastr.error("Devocional es requerido");
                return;
            }

            let formData = new FormData();

            formData.append('idcuestionario', idCuestionarioFila);
            formData.append('devocional', editorDataDescripcionEdit);
            openLoading();

            axios.post('/admin/planbloquedetalle/actualizar/devocional', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        Swal.fire({
                            title: "Devocional Actualizado",
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

        function vistaAtrasPlanesBloquesDeta(){
            let idplanbloquedeta = {{ $idplanbloquedetalle }};
            window.location.href="{{ url('/admin/planbloquedetalle/vista') }}/" + idplanbloquedeta;
        }

    </script>


@endsection
