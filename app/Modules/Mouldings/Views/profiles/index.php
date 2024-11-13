<?= $this->extend('themes/modern/templates/portal-crud') ?>
<?= $this->section('content') ?>
<style>
    label {
        color: white !important;   
    }
</style>
<div class="container-xxl">
<div class="row justify-content-center">
            <h1><?= $title ?></h1>
</div>
<div class="row">
           <div class="col-sm-12 mb-3">
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">NEW</button>
           </div>
           <div class="col-sm-12 mt-1">
               <div class="table-responsive">
                   <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
                       <thead>
                           <tr>
                              <th>ID</th>
                               <th>Name</th>
                              <th>Slug</th>
                                <th>Description</th>
                           </tr>
                       </thead>
                </table>
            </div>
        </div>
      </div>
</div>
</div>


      <script>
$(document).ready(function() {

    var table = new DataTable('#table-data', {
        ajax: {
        url: '/profiles/read',
        dataSrc: ''
    },
    columns: [
        // Render the ID as a clickable link that launches the edit modal
        {data: 'id', render: function(data) {
            return '<a href="#" data-id="'+data+'" class="btn-edit">'+data+'</a>';
        }},
        {data: 'name'},
        {data: 'slug'},
        {data: 'description'},
    ],
    paging: true,
    searching: true,
    ordering: false,
    responsive: true
});

// Create a new record when form is submitted via AJAX

$("body").on("submit", "#form-create", function(e) {
     e.preventDefault();
    $.ajax({
        url: '<?= base_url('profiles/create') ?>',
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
        url: '<?= base_url('profiles/delete') ?>',
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

// Launch the edit modal when the id link is clicked
$('#table-data').on('click', 'a.btn-edit', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: '<?= base_url('profiles/edit') ?>',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(result) {
            if(result.status) {
                $("#modal-edit").modal("show");
                $("#modal-edit input[name='id']").val(result.data.id);
                $("#modal-edit input[name='name']").val(result.data.name);
                $("#modal-edit input[name='slug']").val(result.data.slug);
                $("#modal-edit input[name='description']").val(result.data.description);
                $("#modal-edit button[name='btn-delete']").attr('data-id', result.data.id);
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
        url: '<?= base_url('profiles/update') ?>',
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
<?php include( 'components/edit.php' ); ?>
<?= $this->endSection(); ?>