<?= $this->extend('themes/modern/templates/portal-crud') ?>
<?= $this->section('content') ?>
<style>
    label {
        color: white !important;   
    }
    .page-content {
        padding: 10px 20px;
    }
    h1.center {
       /* make the text adjustable in width based on screensize with clamp */
        font-size: clamp(1.5rem, 5vw, 2rem);
        color: white;
        margin-bottom:20px;
    }
    .add-new {
       width: auto;
       text-align: left;
       font-size: 0.9em;
       font-weight: bold;
       padding-left:2px;
       margin-left:15px;
       margin-bottom: -40px;
       z-index:2;
    }
    .add-new svg {
        width: 25px;
        height: 25px;
        font-weight: bold;
    }
    .table-responsive {
     /*  add negative margin so the add new button is perfectly aligned with the search bar in the table */
        margin-top: 0;
        z-index: 1;
      
    }
</style>
<div class="page-content">
    <div class="row">
                <h1 class="center"><?= $title ?></h1>
    </div>
    <div class="row">
           <form id="search" method="post">
                    <div class=" row mb-4">
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_status">Material</label>
                            <select class="form-select" id="search_status" name="search_status" v-model="filter_status" v-on:change="onFilterStatusChange">
                                <option value="0">All</option>
                                <option value="poplar">Poplar (Solid)</option>
                                <option value="fj-poplar">Poplar (Finger Joint)</option>
                                <option value="cherry">Cherry</option>
                                <option value="walnut">Walnut</option>
                            </select>
                        </div>
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_period">Length</label>
                            <select class="form-select" id="search_period" name="search_period" v-model="filter_period" v-on:change="onFilterPeriodChange">
                                <option value="0">All</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                            </select>
                        </div>
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_um">UM</label>
                            <select class="form-select" id="search_um" name="search_um" v-model="filter_um" v-on:change="onFilterUmChange">
                                <option value="0">Any</option>
                                <option value="pc">PC</option>
                                <option value="lf">LF</option>
                            </select>

                    </div>
                </form>
        </div>
    <div class="row">
                <button type="button" class="add-new btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modal-create"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>ADD NEW</button>
               <div class="table-responsive">
                   <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
                       <thead>
                           <tr>
                              <th>ID</th>
                               <th>SKU</th>
                               <th>QTY</th>
                               <th>UM</th>
                               <th>Description</th>
                               <th>Length</th>
                               <th>Price</th>
                               <th>Photos</th>
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
        url: '/overstock/read',
        dataSrc: ''
    },
    columns: [
        // Render the ID as a clickable link that launches the edit modal
        {data: 'id', render: function(data) {
            return '<a href="#" data-id="'+data+'" class="btn-edit">'+data+'</a>';
        }},
        {data: 'sku'},
        {data: 'qty'},
        {data: 'um'},
        {data: 'description'},
        {data: 'length'},
        {data: 'price'},
        // Render the image link as a clickable image
        {data: 'photos', render: function(data) {
            return '<a href="'+data+'" target="_blank"><img src="'+data+'" width="50" height="60"></a>';
        }},
    ],
    paging: false,
    searching: true,
    ordering: false,
    layout: {
        responsive: true
    }
});

// Create a new record when form is submitted via AJAX

$("body").on("submit", "#form-create", function(e) {
     e.preventDefault();
    $.ajax({
        url: '<?= base_url('overstock/create') ?>',
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
        url: '<?= base_url('overstock/delete') ?>',
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
        url: '<?= base_url('overstock/edit') ?>',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(result) {
            if(result.status) {
                $("#modal-edit").modal('show');
                $("#form-edit input[name='id']").val(result.data.id);
                $("#form-edit input[name='sku']").val(result.data.sku);
                $("#form-edit input[name='qty']").val(result.data.qty);
                $("#form-edit input[name='um']").val(result.data.um);
                $("#form-edit input[name='description']").val(result.data.description);
                $("#form-edit input[name='length']").val(result.data.length);
                $("#form-edit input[name='price']").val(result.data.price);
                $("#form-edit input[name='photos']").val(result.data.photos);
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
        url: '<?= base_url('overstock/update') ?>',
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