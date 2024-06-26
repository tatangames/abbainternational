<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 8%">Pais</th>
                                <th style="width: 8%">Depar.</th>
                                <th style="width: 8%">Iglesia</th>
                                <th style="width: 8%">Nombre</th>
                                <th style="width: 8%">Género</th>
                                <th style="width: 8%">Correo</th>
                                <th style="width: 8%">Imagen</th>
                                <th style="width: 4%">Opciones</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($listado as $dato)
                                <tr>
                                    <td style="width: 8%">{{ $dato->nompais }}</td>
                                    <td style="width: 8%">{{ $dato->nomdepa }}</td>
                                    <td style="width: 8%">{{ $dato->iglesia }}</td>
                                    <td style="width: 8%">{{ $dato->unido }}</td>
                                    <td style="width: 8%">{{ $dato->genero }}</td>
                                    <td style="width: 8%">{{ $dato->correo }}</td>
                                    <td style="width: 8%">
                                        @if($dato->imagen != null)
                                            <center><img alt="Imagenes" src="{{ url('storage/archivos/'.$dato->imagen) }}" width="100px" height="100px" /></center>
                                        @endif
                                    </td>

                                    <td style="width: 4%">
                                        <button type="button" class="btn btn-primary btn-xs" onclick="informacion({{ $dato->id }})">
                                            <i class="fas fa-eye" title="Respuestas"></i>&nbsp; Respuestas
                                        </button>
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
