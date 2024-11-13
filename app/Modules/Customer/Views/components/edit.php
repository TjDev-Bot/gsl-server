<div class="modal fade" id="modal-update" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <form id="form-update" method="post" autocomplete="off">
                <input type="hidden" name="id" value="'.$result->id.'"/>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-message">
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="'.$result->name.'" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="'.$result->email.'" required>
                    </div>
                    <div class="form-group">
                        <label>Mobile No.</label>
                        <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="'.$result->mobile_number.'" placeholder="Mobile No." maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="'.$result->address.'" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
           </div>
       </div>
   </div>