<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 3%">Fecha Devocional</th>
                                <th style="width: 6%">Visible</th>
                                <th style="width: 6%">Texto Personalizado</th>
                                <th style="width: 6%">Título Personalizado</th>
                                <th style="width: 4%">Opciones</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($listado as $dato)
                                <tr>
                                    <td style="width: 3%">{{ $dato->fechaFormat }}</td>

                                    <td style="width: 6%">
                                        @if($dato->visible == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>

                                    <td style="width: 6%">
                                        @if($dato->texto_personalizado == 1)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>

                                    <td style="width: 3%">{{ $dato->textoPersonalizado }}</td>

                                    <td style="width: 4%">
                                        <button style="margin: 8px" type="button" class="btn btn-info btn-xs" onclick="informacionEditar({{ $dato->id }})">
                                            <i class="fas fa-eye" title="Editar"></i>&nbsp; Editar
                                        </button>

                                        <button style="margin: 8px" type="button" class="btn btn-success btn-xs" onclick="informacionDetalleBloque({{ $dato->id }})">
                                            <i class="fas fa-eye" title="Detalle"></i>&nbsp; Detalle
                                        </button>


                                        @if($dato->visible == 1)
                                            <button style="margin: 8px" type="button" class="btn btn-danger btn-xs" onclick="preguntaDeshabilitar({{ $dato->id }})">
                                                <i class="fas fa-edit" title="Deshabilitar"></i>&nbsp; Deshabilitar
                                            </button>
                                        @else
                                            <button style="margin: 8px" type="button" class="btn btn-success btn-xs" onclick="preguntaActivar({{ $dato->id }})">
                                                <i class="fas fa-edit" title="Activar"></i>&nbsp; Activar
                                            </button>
                                        @endif


                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $(function () {
        $("#tabla").DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, 100, 150, -1], [10, 25, 50, 100, 150, "Todo"]],
            "language": {

                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }

            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });
    });


</script>
