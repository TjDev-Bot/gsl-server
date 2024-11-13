<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Update</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit" method="post" autocomplete="off">
                    <input type="hidden" id="id" name="id" placeholder="ID" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" id="unit_id" name="unit_id" class="form-control" placeholder="Unit ID" />
                                <label for="unit_id">Unit ID</label>
                            </div>
                        </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="sku" name="sku" class="form-control" placeholder="SKU" />
                                    <label for="sku">SKU</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="po" name="po" class="form-control" placeholder="PO" />
                                    <label for="po">PO</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="unit_no" name="unit_no" class="form-control" placeholder="Unit #" />
                                    <label for="unit_no">Unit #</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                 <input type="text" id="vendor" name="vendor" class="form-control" placeholder="Vendor" />
                                    <label for="vendor">Vendor</label>   
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="qty" name="qty" class="form-control" placeholder="Qty" />
                                    <label for="qty">Qty</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="length" name="length" class="form-control" placeholder="Length" />
                                    <label for="length">Length</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="total" name="total" class="form-control" placeholder="Total" />
                                    <label for="total">Total</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="damage" name="damage" class="form-control" placeholder="Damage" />
                                    <label for="damage">Damage</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                        <div class="col">
                                <div class="form-group">
                                    <input type="date" id="date_in" name="date_in" class="form-control" placeholder="Date In" />
                                    <label for="date_in">Date In</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="date" id="date_out" name="date_out" class="form-control" placeholder="Date Out" />
                                    <label for="date_out">Date Out</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" id="customer" name="customer" class="form-control" placeholder="Customer" />
                                    <label for="customer">Customer</label>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                                <div class="form-group">
                                   <textarea id="notes" name="notes" class="form-control" placeholder="Notes"></textarea>
                                    <label for="notes">Notes</label>
                                </div>
                            </div>
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