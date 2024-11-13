<div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered" role="document">
           <div class="modal-content">
               <form id="form-create" method="post" autocomplete="off">
                   <div class="modal-header">
                       <h5 class="modal-title" id="exampleModalLabel">New</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <div class="modal-message">
                       </div>
                       <div class="form-group">
                           <label>Name</label>
                           <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                       </div>
                       <div class="form-group">
                           <label>Email</label>
                           <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                       </div>
                       <div class="form-group">
                           <label>Mobile No.</label>
                           <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Mobile No." maxlength="10" required>
                       </div>
                       <div class="form-group">
                           <label>Address</label>
                           <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="submit" class="btn btn-primary">Submit</button>
                   </div>
               </form>
           </div>
       </div>
   </div>