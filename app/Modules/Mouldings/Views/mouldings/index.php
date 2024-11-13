<?= $this->extend('themes/modern/templates/portal-crud') ?>
<?= $this->section('content') ?>
<div class="page-content">
<div class="row">
                <h1 class="center"><?= $title ?></h1>
</div>
<div class="row">
                <button type="button" class="add-new btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modal-create">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
</svg>ADD NEW</button>
               <div class="table-responsive">
                   <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
                       <thead>
                           <tr>
                              <th>ID</th>
                               <th>SKU</th>
                                <th>Profile</th>
                                <th>Thickness</th>
                                <th>Width</th>
                                <th>HDWD Rip SKU</th>
                                <th>HDWD Pieces</th>
                                <th>Radiata Rip SKU</th>
                                <th>Radiata Pieces</th>
                           </tr>
                       </thead>
                </table>
        </div>
      </div>
</div>
<script>
$(document).ready(function() {

    var table = new DataTable('#table-data', {
        ajax: {
        url: '/<?=$variable?>/read',
        dataSrc: ''
    },
    columns: [
        // Render the ID as a clickable link that launches the edit modal
        {data: 'id', render: function(data) {
            return '<a href="#" data-id="'+data+'" class="btn-edit">'+data+'</a>';
        }},
        {data: 'sku'},
        {data: 'profile'},
        {data: 'friendly_thickness'},
        {data: 'friendly_width'},
        {data: 'hdwd_ripsku'},
        {data: 'hdwd_pieces'},
        {data: 'radiata_ripsku'},
        {data: 'radiata_pieces'},
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
        url: '<?= base_url($variable . '/edit') ?>',
        type: 'POST',
        data: {id: id},
        type: "POST",
        dataType: "json",
        success: function(result) {
            if(result.status) {
                $("#modal-edit").modal('show');
                $("#form-edit input[name='id']").val(result.data.id);
                $("#form-edit input[name='sku']").val(result.data.sku);
                $("#form-edit input[name='thickness']").val(result.data.thickness);
                $("#form-edit input[name='width']").val(result.data.width);
                $("#form-edit input[name='profile']").val(result.data.profile);
                $("#form-edit input[name='description']").val(result.data.description);
                $("#form-edit input[name='mill_drawing']").val(result.data.mill_drawing);
                $("#form-edit input[name='thumbnail_image']").val(result.data.thumbnail_image);
                $("#form-edit input[name='hdwd_ripsku']").val(result.data.hdwd_ripsku);
                $("#form-edit input[name='hdwd_pieces']").val(result.data.hdwd_pieces);
                $("#form-edit input[name='radiata_ripsku']").val(result.data.radiata_ripsku);
                $("#form-edit input[name='radiata_pieces']").val(result.data.radiata_pieces);
                $("#modal-edit button[name='btn-delete']").attr('data-id', result.data.id);
            } else {
                $(".modal-message").html(result.message);
            }
        }
    });
});

// Create a new record when form is submitted via AJAX

$("body").on("submit", "#form-create", function(e) {
     e.preventDefault();
    $.ajax({
        url: '<?= base_url($variable . '/create') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        encode: true,
        success: function(result) {
            if(result.status) {
                table.ajax.reload();
                $("#form-create")[0].reset();
                $("#modal-create").modal("hide");
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
        url: '<?= base_url($variable . '/delete') ?>',
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
        url: '<?= base_url($variable . '/update') ?>',
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
<?php include( 'components/create.php' ); ?>
<?php //include( 'components/edit.php' ); ?>
<?= $this->endSection(); ?>