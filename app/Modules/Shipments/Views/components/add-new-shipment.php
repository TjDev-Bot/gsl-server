<div class="modal fade" id="shipping_list_dialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered text-left" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 v-if="!editing" class="modal-title" id="exampleModalCenterTitle">Adding New Shipment</h6>
                <h6 v-if="editing" class="modal-title" id="exampleModalCenterTitle">Editing Shipment</h6>
                <button type="button" class="btn-close" v-on:click="handleAddNewClose" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <label for="pack_num" class="form-label">Shipment #</label>
                        <template v-if="post_id==0">
                            <input id="pack_num" class="form-control" placeholder="Updates on save..." type="text" autocomplete="off" readonly  />
                        </template>
                        <template v-else>
                            <input id="pack_num" class="form-control" :value="ship_no" type="text" autocomplete="off" readonly  />
                        </template>
                    </div>
                    <div class="col" v-if="post_id!=0">
                        <label for="created" class="form-label">Created</label>
                        <template>
                            <input id="created" class="form-control" type="text" autocomplete="off" readonly v-model="created" />
                        </template>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <label for="ship_to" class="form-label">Ships To</label>
                        <select class="form-select" id="ship_to" name="ship_to" v-model="ship_to">
                            <?//php $locations = gsm_get_all_locations(); ?>
                            <?//php foreach( $locations as $location ) { ?>
                                <option value="<?//php echo esc_attr( $location->slug ); ?>"><?//php echo esc_html( 'GSL ' . $location->name ); ?></option>
                            <?//php } ?>
                            <option value="CUSTOM">Custom</option>
                        </select>
                    </div>
                        <div class="col">
                        <label for="decodedResponse" class="form-label">Status</label>
                        <select class="form-select" id="shipment_status" name="shipment_status" v-model="shipment_status">
                                <option value="pending">Not shipped</option>
                                <option value="shipped">Shipped</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3" v-if="ship_to=='CUSTOM'">
                    <div class="col">
                        <label for="ship_contact" class="form-label">Contact</label>
                        <input id="ship_contact" class="form-control" type="text" autocomplete="off" v-model="ship_contact" />
                    </div>
                    <div class="col">
                        <label for="ship_city" class="form-label">Street Address</label>
                        <input id="ship_city" class="form-control" type="text" autocomplete="off" v-model="ship_street" />
                    </div>
                    <div class="col">
                        <label for="ship_city" class="form-label">City, State</label>
                        <input id="ship_city" class="form-control" type="text" autocomplete="off" v-model="ship_city" />
                    </div>
                </div>

                <div style="height: 300px!important; overflow: scroll;">
                    <table class="table table-bordered shipment-table table-sm">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th>&nbsp;</th>
                                <th>Pack #</th>
                                <th>Type</th>
                                <th>PO</th>
                                <th>LF</th>
                                <th>PCs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="!contained_packs.length">
                                <tr><td colspan="6" class="text-center">No Results</td></tr>
                            </template>
                            <template v-else>
                                <tr v-for="result in contained_packs" v-bind:data-num="result.pack_info.id">
                                    <td class="selected text-center" v-bind:data-num="result.pack_info.id"><input type="checkbox" class="form-check-input" autocomplete="off" /></td>
                                    <td>{{result.pack_info.name}}</td>
                                    <td>{{result.friendly_pack_type}}</td>
                                    <td>{{result.pack_po}}</td>
                                    <td>{{result.pack_info.total_length}}</td>
                                    <td>{{result.pack_info.total_pieces}}</td>
                                </tr> 
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button id="add_new_pack_item" class="float-start btn btn-outline-warning" v-on:click="searchForPacks"><i class="fas fa-plus"></i> Add Pack</button>
                <button id="remove_pack_item" class="float-left btn btn-outline-warning" v-on:click="removePacks"><i class="fas fa-minus"></i> Remove Pack</button>
                <button v-if="editing" id="discard_or_delete" class="float-left btn btn-outline-danger" v-on:click="deleteShipment"><i class="fas fa-delete"></i> Delete</button>
                <button v-if="!editing" id="discard_or_delete" class="float-left btn btn-outline-danger" v-on:click="discardChanges"><i class="fas fa-delete"></i> Discard</button>
                <button type="button" class="btn btn-secondary" v-on:click="doShipmentSave">Save &amp; Close</button>
            </div>
        </div>
    </div>
</div>
