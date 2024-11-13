<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
               <div class="modal-header">
               <h1 class="modal-title fs-5" id="exampleModalLabel">Update</h1>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="modal-message">
                   </div>
                 <form id="form-edit" method="post" autocomplete="off">
               <input type="hidden" name="id" />
                   <div class="form-group">
                       <label>Code</label>
                       <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>
                   </div>
                   <div class="form-group">
                       <label>Name</label>
                       <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                   </div>
                   <div class="form-group">
                       <label>Address</label>
                       <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
                   </div>
                   <div class="form-group">
                       <label>City</label>
                       <input type="text" class="form-control" id="city" name="city" placeholder="City" required>   
               </div>
                   <div class="form-group">
                       <label>State</label>
                       <input type="text" class="form-control" id="state" name="state" placeholder="State" required>
                   </div>
                   <div class="form-group">
                       <label>Zip</label>
                       <input type="text" class="form-control" id="zip" name="zip" placeholder="Zip" required>
                   </div>
               <div class="modal-footer">
               <button type="button" name="btn-delete" class="btn btn-sm btn-danger" data-id="id" title="Delete">Delete</button> 
                   <button type="submit" class="btn btn-primary">Submit</button>
               </div>
              </form>
</div>
          </div>
      </div>
  </div>
