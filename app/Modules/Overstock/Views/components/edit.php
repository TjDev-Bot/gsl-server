<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
               <div class="modal-header">
               <h1 class="modal-title fs-5" id="exampleModalLabel">Update</h1>
                   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   <div class="modal-message">
                   </div>
                 <form id="form-edit" method="post" autocomplete="off">
               <input type="hidden" name="id" />

               <div class="form-group">
                           <label>ID</label>
                           <input type="text" class="form-control" id="id" name="id" placeholder="ID" required>
                          </div>
                            <div class="form-group">
                            <label>SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" placeholder="SKU" required>
                            </div>
                            <div class="form-group">
                            <label>QTY</label>
                            <input type="text" class="form-control" id="qty" name="qty" placeholder="QTY" required>
                            </div>
                            <div class="form-group">
                            <label>UM</label>
                            <input type="text" class="form-control" id="um" name="um" placeholder="UM" required>
                            </div>
                            <div class="form-group">
                            <label>Description</label>
                            <input type="text" class="form-control" id="description" name="description" placeholder="Description" required>
                            </div>
                            <div class="form-group">
                            <label>Length</label>
                            <input type="text" class="form-control" id="length" name="length" placeholder="Length" required>
                            </div>
                            <div class="form-group">
                            <label>Price</label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="Price" required>
                            </div>
                            <div class="form-group">
                            <label>Photos</label>
                            <input type="text" class="form-control" id="photos" name="photos" placeholder="Photos" required>
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