<?= $this->extend('themes/modern/templates/portal-crud') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="row">
        <h1 class="center"><?= $title ?></h1>
    </div>
    <div class="row">
<div class="table-responsive">
    <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
        <thead>
            <tr>
                <th>Unit ID</th>
                <th>SKU</th>
                <th>GSLP PO</th>
                <th>Supplier</th>
                <th>Qty/PC</th>
                <th>Length</th>
                <th>Total</th>
                <th>Damage (PC)</th>
                <th>Date In</th>
                <th>Date Out</th>
                <th>Customer</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
    </table>
</div>
</div><!-- .page-content -->
</div><!-- .row -->
<script>
$(document).ready(function() {

    var table = new DataTable('#table-data', {
        ajax: {
        url: '/<?=$route?>/read',
        dataSrc: ''
    },
    columns: [
        {data: 'unit_id'},
        {data: 'sku'},
        {data: 'po_no'},
        {data: 'supplier_code'},
        {data: 'unit_qty'},
        {data: 'length'},
        {data: 'total'},
        {data: 'damage'},
        {data: 'date_in'},
        {data: 'date_out'},
        {data: 'customer'},
        {data: 'notes'},
        {data: null, render: function(data, type, row) {
            return '<div class="dropdown">' +
                '<a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                '</a>' +
                '<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">' +
                '<a class="dropdown-item btn-edit" href="#" data-id="' + row.id + '">Edit</a>' +
                '</div>' +
                '</div>';
        }}
    ],
    paging: true,
    searching: true,
    ordering: true,
    responsive: true
});

// Launch the edit modal when the id link is clicked
$('#table-data').on('click', 'a.btn-edit', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: '<?= base_url($route . '/edit') ?>',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(result) {
            if(result.status) {
                $("#modal-edit").modal('show');
                $("#form-edit input[name='id']").val(result.data.id);
                $("#form-edit input[name='unit_id']").val(result.data.unit_id);
                $("#form-edit input[name='sku']").val(result.data.sku);
                $("#form-edit input[name='po']").val(result.data.po);
                $("#form-edit input[name='unit_no']").val(result.data.unit_no);
                $("#form-edit input[name='supplier']").val(result.data.supplier);
                $("#form-edit input[name='qty']").val(result.data.qty);
                $("#form-edit input[name='length']").val(result.data.length);
                $("#form-edit input[name='total']").val(result.data.total);
                $("#form-edit input[name='damage']").val(result.data.damage);
                $("#form-edit input[name='date_in']").val(result.data.date_in);
                $("#form-edit input[name='date_out']").val(result.data.date_out);
                $("#form-edit input[name='customer']").val(result.data.customer);
                $("#form-edit textarea[name='notes']").val(result.data.notes);
                $("#modal-edit button[name='btn-delete']").attr('data-id', result.data.id);
            } else {
                $(".modal-message").html(result.message);
            }
        }
    });
});


// Delete the record when the delete button is clicked on the edit modal 

$('#modal-edit').on('click', 'button[name="btn-delete"]', function() {
    var id = $(this).data('id');
    $.ajax({
        url: '<?= base_url($route . '/delete') ?>',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(result) {
            if(result.status) {
                table.ajax.reload();
                $("#modal-edit").modal("hide");
            } else {
                $(".modal-message").html(result.message);
            }
        }
    });
});

// Update the record when the form is submitted via AJAX
$("body").on("submit", "#form-edit", function(e) {
     e.preventDefault();
    $.ajax({
        url: '<?= base_url($route . '/update') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        encode: true,
        success: function(result) {
            if(result.status) {
                table.ajax.reload();
                $("#form-edit")[0].reset();
                $("#modal-edit").modal("hide");
            } else {
                $(".modal-message").html(result.message);
            }
        }
    });
});
    
    });
</script>
<?php include( 'components/edit.php' ); ?>
<?= $this->endSection(); ?>