<?= $this->include('julio101290\boilerplate\Views\load\select2') ?>
<?= $this->include('julio101290\boilerplate\Views\load\datatables') ?>
<?= $this->include('julio101290\boilerplate\Views\load\nestable') ?>
<?= $this->extend('julio101290\boilerplate\Views\layout\index') ?>
<?= $this->section('content') ?>
<?= $this->include('julio101290\boilerplateConsumptionCenter\Views\modulesConsumptioncenter/modalCaptureConsumptioncenter') ?>
<div class="card card-default">
    <div class="card-header">
        <div class="float-right">
            <div class="btn-group">
                <button class="btn btn-primary btnAddConsumptioncenter" data-toggle="modal" data-target="#modalAddConsumptioncenter">
                    <i class="fa fa-plus"></i> <?= lang('consumptioncenter.add') ?>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="tableConsumptioncenter" class="table table-striped table-hover va-middle tableConsumptioncenter">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('consumptioncenter.fields.idEmpresa') ?></th>
                                <th><?= lang('consumptioncenter.fields.descripcion') ?></th>
                                <th><?= lang('consumptioncenter.fields.created_at') ?></th>
                                <th><?= lang('consumptioncenter.fields.updated_at') ?></th>
                                <th><?= lang('consumptioncenter.fields.deleted_at') ?></th>

                                <th><?= lang('consumptioncenter.fields.actions') ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script>
    var tableConsumptioncenter = $('#tableConsumptioncenter').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        order: [[1, 'asc']],
        ajax: {
            url: '<?= base_url('admin/consumptioncenter') ?>',
            method: 'GET',
            dataType: "json"
        },
        columnDefs: [{
                orderable: false,
                targets: [6],
                searchable: false,
                targets: [6]
            }],
        columns: [{'data': 'id'},
            {'data': 'nombreEmpresa'},
            {'data': 'descripcion'},
            {'data': 'created_at'},
            {'data': 'updated_at'},
            {'data': 'deleted_at'},

            {
                "data": function (data) {
                    return `<td class="text-right py-0 align-middle">
                         <div class="btn-group btn-group-sm">
                             <button class="btn btn-warning btnEditConsumptioncenter" data-toggle="modal" idConsumptioncenter="${data.id}" data-target="#modalAddConsumptioncenter">  <i class=" fa fa-edit"></i></button>
                             <button class="btn btn-danger btn-delete" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                         </div>
                         </td>`
                }
            }
        ]
    });

    $(document).on('click', '#btnSaveConsumptioncenter', function (e) {
        var idConsumptioncenter = $("#idConsumptioncenter").val();
        var idEmpresa = $("#idEmpresa").val();
        var descripcion = $("#descripcion").val();

        $("#btnSaveConsumptioncenter").attr("disabled", true);
        var datos = new FormData();
        datos.append("idConsumptioncenter", idConsumptioncenter);
        datos.append("idEmpresa", idEmpresa);
        datos.append("descripcion", descripcion);

        $.ajax({
            url: "<?= base_url('admin/consumptioncenter/save') ?>",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                if (respuesta?.message?.includes("Guardado") || respuesta?.message?.includes("Actualizado")) {
                    Toast.fire({
                        icon: 'success',
                        title: respuesta.message
                    });
                    tableConsumptioncenter.ajax.reload();
                    $("#btnSaveConsumptioncenter").removeAttr("disabled");
                    $('#modalAddConsumptioncenter').modal('hide');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: respuesta.message || "Error desconocido"
                    });
                    $("#btnSaveConsumptioncenter").removeAttr("disabled");
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: jqXHR.responseText
            });
            $("#btnSaveConsumptioncenter").removeAttr("disabled");
        });
    });

    $(".tableConsumptioncenter").on("click", ".btnEditConsumptioncenter", function () {
        var idConsumptioncenter = $(this).attr("idConsumptioncenter");
        var datos = new FormData();
        datos.append("idConsumptioncenter", idConsumptioncenter);
        $.ajax({
            url: "<?= base_url('admin/consumptioncenter/getConsumptioncenter') ?>",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (respuesta) {
                $("#idConsumptioncenter").val(respuesta["id"]);
                $("#idEmpresa").val(respuesta["idEmpresa"]).trigger("change");
                $("#descripcion").val(respuesta["descripcion"]);

            }
        });
    });

    $(".tableConsumptioncenter").on("click", ".btn-delete", function () {
        var idConsumptioncenter = $(this).attr("data-id");
        Swal.fire({
            title: '<?= lang('boilerplate.global.sweet.title') ?>',
            text: "<?= lang('boilerplate.global.sweet.text') ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<?= lang('boilerplate.global.sweet.confirm_delete') ?>'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: `<?= base_url('admin/consumptioncenter') ?>/` + idConsumptioncenter,
                    method: 'DELETE',
                }).done((data, textStatus, jqXHR) => {
                    Toast.fire({
                        icon: 'success',
                        title: jqXHR.statusText,
                    });
                    tableConsumptioncenter.ajax.reload();
                }).fail((error) => {
                    Toast.fire({
                        icon: 'error',
                        title: error.responseJSON.messages.error,
                    });
                });
            }
        });
    });

    $(function () {
        $("#modalAddConsumptioncenter").draggable();
    });
</script>
<?= $this->endSection() ?>