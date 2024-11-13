<?= $this->extend('layouts/portal-tables') ?>
<?= $this->section('content') ?>
<div class="container-xxl">
       <div class="row">
           <div class="col-sm-12 message">
            <h1><?= $content_title ?></h1>
           </div>
           <div class="col-sm-12">
               <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create">New</button>
           </div>
           <div class="col-sm-12 mt-1">
               <div class="table-responsive">
                   <table id="table-data" class="table table-dark table-sm table-bordered table-striped table-hover" width="100%">
                       <thead>
                           <tr>
                               <th>#</th>
                               <th>Name</th>
                               <th>Email</th>
                               <th>Mobile</th>
                               <th>Address</th>
                               <th>Action</th>
                           </tr>
                       </thead>
                   </table>
               </div>
           </div>
       </div>
      </div>
        <?php include( 'components/create.php' ); ?>
        <?php // include( 'components/edit.php' ); ?>
   <script>
   $(document).ready(function() {
       var table_data = $("#table-data").DataTable({
               "processing":true,
               "serverSide":true,
               "order":[],
       "ajax": {
           url : '<?php echo base_url("customer/datatable") ?>',
                   type:"POST"
       },
       "columnDefs":[
       {
           "targets":[0],
           "orderable":false,
       },  
               ],
           });
    $('#table-data tbody').on( 'click', 'button', function () {
           let id = $(this).attr('data-id');
           if(this.name == "btn-delete") {
               var isDelete = confirm("Are you sure you want to delete this?");
               if(isDelete) {
                   $.post("<?php echo base_url("customer/delete"); ?>", {id: id}, function( result ) {
                       $(".message").html(result.message);
                       if(result.status) {
                           table_data.ajax.reload();
                       }
                   }, 'json');
               }
           } if(this.name == "btn-edit") {
               $.post("<?php echo base_url("customer/edit"); ?>", {id: id}, function( result ) {
                   $(".edit").html(result);
                   $("#modal-update").modal("show");
               });
           }
       });

       $("body").on("submit", "#form-create", function(e) {
           e.preventDefault();
           let data = $(this).serialize();
           $.post('<?php echo base_url("customer/create"); ?>', data, function(result) {
               if(result.status) {
                   $(".message").html(result.message);
                   table_data.ajax.reload();
                   $("#form-create")[0].reset();
                   $("#modal-create").modal("hide");
               } else {
                   $(".modal-message").html(result.message);
               }
           }, 'json');
       });
 $("body").on("submit", "#form-update", function(e) {
           e.preventDefault();
           let data = $(this).serialize();
           $.post('<?php echo base_url("customer/update"); ?>', data, function(result) {
               if(result.status) {
                   $(".message").html(result.message);
                   table_data.ajax.reload();
                   $("#form-update")[0].reset();
                   $("#modal-update").modal("hide");
               } else {
                   $(".modal-message").html(result.message);
               }
           }, 'json');
       });
   });
   </script>
<?= $this->endSection() ?>