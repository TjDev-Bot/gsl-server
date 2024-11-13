<?= $this->extend('themes/serene/templates/portal-crud') ?>
<?= $this->section('content') ?>
<style>
    label {
        color: white !important;   
    }
</style>
       <div class="row">
           <div class="col-sm-12 message">
            <h1 class="center"><?= $title ?></h1>
           </div>
           <div class="col-sm-12 mb-4">
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">NEW</button>
           </div>
           <div class="col-sm-12 mt-1">
               <div class="table-responsive">
                   <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
                       <thead>
                           <tr>
                            <th>ID</th>
                              <th>Code</th>
                               <th>Name</th>
                               <th>Address</th>
                               <th>City</th>
                               <th>State</th>
                               <th>Zip</th>
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
        url: '/locations/read',
        dataSrc: 'data'
    },
    columns: [
        // Render the ID as a clickable link that launches the edit modal

         // Render the ID as a clickable link that launches the edit modal
         {data: 'id'},

         
          {data: 'code'},
        // Render the ID as a clickable link that launches the edit modal
        
        
        {data: 'name'},
        {data: 'address'},
        {data: 'city'},
        {data: 'state'},
        {data: 'zip'},
    ],
    paging: false,
    searching: false,
    ordering: false,
    layout: {
        responsive: true
    }
});

// Create a new record when form is submitted via AJAX

$("body").on("submit", "#form-create", function(e) {
     e.preventDefault();
    $.ajax({
        url: '<?= base_url('locations/create') ?>',
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
        url: '<?= base_url('locations/delete') ?>',
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

// Launch the edit modal when the id link is clicked and add the id to the delete button in the modal
$('#table-data').on('click', 'a.btn-edit', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    console.log(id);
    $.ajax({
        url: '<?= base_url('locations/edit') ?>',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(result) {
            if(result.status) {
                $("#modal-edit").modal("show");
                $("#form-edit input[name='id']").val(result.data.id);
                $("#form-edit input[name='code']").val(result.data.code);
                $("#form-edit input[name='name']").val(result.data.name);
                $("#form-edit input[name='address']").val(result.data.address);
                $("#form-edit input[name='city']").val(result.data.city);
                $("#form-edit input[name='state']").val(result.data.state);
                $("#form-edit input[name='zip']").val(result.data.zip);
                // Add the id value to the button data attribute "data-id"
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
        url: '<?= base_url('locations/update') ?>',
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