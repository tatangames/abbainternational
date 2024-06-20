
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="table" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Título</th>
                            <th>Opciones</th>
                        </tr>
                        </thead>
                        <tbody id="tablecontents">
                        @foreach($listado as $dato)
                            <tr class="row1" data-id="{{ $dato->id }}">

                                <td>{{ $dato->posicion }}</td>
                                <td>{{ $dato->titulo }}</td>


                                <td>
                                    <button style="margin: 8px" type="button" class="btn btn-info btn-xs" onclick="informacionEditar({{ $dato->id }})">
                                        <i class="fas fa-edit" title="Editar"></i>&nbsp; Editar
                                    </button>

                                    <button style="margin: 8px" type="button" class="btn btn-success btn-xs" onclick="vistaDevocional({{ $dato->id }})">
                                        <i class="fas fa-edit" title="Devocional"></i>&nbsp; Devocional
                                    </button>

                                    <button style="margin: 8px" type="button" class="btn btn-success btn-xs" onclick="vistaPreguntas({{ $dato->id }})">
                                        <i class="fas fa-edit" title="Preguntas"></i>&nbsp; Preguntas
                                    </button>

                                    <button style="margin: 8px" type="button" class="btn btn-success btn-xs" onclick="vistaBiblias({{ $dato->id }})">
                                        <i class="fas fa-edit" title="Biblia"></i>&nbsp; Biblia
                                    </button>

                                    <button style="margin: 8px" type="button" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $dato->id }})">
                                        <i class="fas fa-trash" title="Borrar"></i>&nbsp; Borrar
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
</section>

<script type="text/javascript">
    $(document).ready(function() {

        $( "#tablecontents" ).sortable({
            items: "tr",
            cursor: 'move',
            opacity: 0.6,
            update: function() {
                sendOrderToServer();
            }
        });

        function sendOrderToServer() {

            var order = [];
            $('tr.row1').each(function(index,element) {
                order.push({
                    id: $(this).attr('data-id'),
                    posicion: index+1
                });
            });

            openLoading();

            axios.post('/admin/planbloquedetalle/actualizar/posicion',  {
                'order': order
            })
                .then((response) => {
                    closeLoading();
                    toastr.success('Actualizado correctamente');
                    recargar();
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al actualizar');
                });
        }
    });

</script>
