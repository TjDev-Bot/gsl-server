<div class="modal fade" id="packing_list_dialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered text-left" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 v-if="post_id==0" class="modal-title" id="exampleModalCenterTitle">Add New Pack</h6>
                <h6 v-if="post_id>0" class="modal-title" id="exampleModalCenterTitle">Edit Pack</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col">
                        <label for="pack_num" class="form-label">Pack #</label>
                        <input id="pack_num" class="form-control" placeholder="Updates on save..." type="text" autocomplete="off" readonly v-model="pack_num" />
                    </div>
                    <div class="col">
                        <?//php $pack_types = GSM_Packing_List::get_pack_types(); ?>
                        <label for="pack_type" class="form-label">Pack Type</label>
                        <select id="pack_type" class="form-select" autocomplete="off" v-model="pack_type" :disabled="hasPackEntries()">
                            <?//php foreach( $pack_types as $key => $pack_type ) { ?>
                                <option value="<?//php echo esc_attr( $key ); ?>"><?//php echo esc_html( $pack_type ); ?></option>
                            <?//php } ?>
                        </select>
                    </div>
                    <div class="col" v-if="post_id!=0">
                        <label for="created" class="form-label">Created</label>
                        <template>
                            <input id="created" class="form-control" type="text" autocomplete="off" readonly v-model="created" />
                        </template>
                    </div>
                </div>
                <div class="row mb-4" v-show="pack_type=='custom'">
                    <div class="col">
                        <?//php $order_info = GSM_Packing_List::get_gsm_order_list(); ?>
                        <label for="import_from_order" class="import_from_order">Import Order</label>
                        <div class="input-group mb-3">
                            <select id="import_list" class="form-select" autocomplete="off" v-model="import_order_id">
                                <option value="0">Choose Order</option>
                                <?//php foreach( $order_info as $key => $info ) { ?>
                                    <option value="<?//php echo esc_attr( $key ); ?>"><?//php echo esc_html( $info->display_text ); ?></option>
                                <?//php } ?>
                            </select>
                            <button type="button" class="btn btn-secondary" v-on:click="importPackOrder">Import</button>
                        </div>
                    </div>
                        
                </div>

                <select style="display: none" class="form-select form-select-sm" id="sku-list">
                    <option value="0">UNASSIGNED</option>

                    <?//php $skus = GSM_Packing_List::gsm_load_all_packing_skus( false ); ?>
                    <?//php foreach( $skus as $id => $data ) { ?>
                        <option data-contains-length="<?//php echo $data->sku_contains_length; ?>" value="<?//php echo esc_attr( $id ); ?>" data-desc="<?//php echo esc_attr( $data->desc ); ?>"><?//php echo esc_html( $data->title ); ?></option>
                    <?//php } ?>  
                </select>

                <div class="row mb-3">
                    <div class="col">
                        <table class="table table-bordered table-sm pack">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th v-if="pack_type=='custom'" colspan="3">INFO</th>
                                    <th v-if="pack_type!='custom'" colspan="2"></th>
                                    <th colspan="3">CUSTOM</th>
                                    <th colspan="11">LENGTHS</th>
                                    <th colspan="2">TOTALS</th>
                                    <th></th>
                                </tr>
                                <tr class="bg-primary text-white">
                                    <th class="pack-sku">SKU</th>
                                    <th class="pack-notes">DESC</th>
                                    <th class="pack-po" v-if="pack_type=='custom'">PO</th>
                                    <th class="">PCS</th>
                                    <th class="">DESC</th>
                                    <th class="">LEN</th>
                                    <th class="">6</th>
                                    <th class="">7</th>
                                    <th class="">8</th>
                                    <th class="">9</th>
                                    <th class="">10</th>
                                    <th class="">11</th>
                                    <th class="">12</th>
                                    <th class="">13</th>
                                    <th class="">14</th>
                                    <th class="">15</th>
                                    <th class="">16</th>
                                    <th class="">PCS</th>
                                    <th class="">LF</th>
                                    <th class="col-del"></th>
                                </tr>
                            </thead>
                            <tbody> 
                                <tr v-for="(result,index) in add_edit_pack_data" class="real-data">
                                    <td class="pack-sku">
                                        <select class="form-select form-select-sm" :value="result.packed_sku"  v-model="result.packed_sku" v-show="pack_type!='custom'" @change="updateStockNotes(result.packed_sku,index)">
                                            <option value="0">UNASSIGNED</option>

                                            <?//php $skus = GSM_Packing_List::gsm_load_all_packing_skus( false ); ?>
                                            <?//php foreach( $skus as $id => $data ) { ?>
                                                <option data-contains-length="<?//php echo $data->sku_contains_length; ?>" value="<?//php echo esc_attr( $id ); ?>" data-desc="<?//php echo esc_attr( $data->desc ); ?>"><?//php echo esc_html( $data->title ); ?></option>
                                            <?//php } ?>  
                                        </select>

                                        <select class="form-select form-select-sm" :value="result.packed_sku"  v-model="result.packed_sku" v-show="pack_type=='custom'">
                                            <option value="0">UNASSIGNED</option>
                                            <option value="custom">CUSTOM</option>
                                            <option value="s4s">S4S</option>

                                            <?//php $skus = GSM_Packing_List::gsm_load_all_packing_skus( true ); ?>
                                            <?//php foreach( $skus as $id => $data ) { ?>
                                                <option value="<?//php echo esc_attr( $id ); ?>"><?//php echo esc_html( $data->title ); ?></option>
                                            <?//php } ?>  
                                        </select>
                                    </td>
                                     <td class="input-group-sm pack-notes">
                                        <input type="text" class="form-control" v-model="result.notes" autocomplete="off" :readonly="pack_type!='custom'">  
                                    </td>
                                    <td class="input-group-sm pack-po" v-if="pack_type=='custom'">
                                        <input type="text" class="form-control" v-model="result.po" autocomplete="off" v-on:keyup="recomputeTable"> 
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.pcs" autocomplete="off" min="0" max="100000" step="1"  v-on:change="recomputeTable">  
                                    </td>
                                    <td class="input-group-sm pack-cus">
                                        <input type="text" class="form-control" v-model="result.desc" autocomplete="off" :readonly="checkCustom(index)">  
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.len" autocomplete="off" min="0" max="100000" step="1"  v-on:change="recomputeTable" :readonly="checkCustom(index)">  
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_6" autocomplete="off" min="0" max="100000" step="1" v-on:change="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_7" autocomplete="off" min="0" max="100000" step="1" v-on:change="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_8" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_9" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_10" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_11" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_12" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_13" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_14" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_15" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.length_16" autocomplete="off" min="0" max="100000" step="1" v-on:keyup="recomputeTable" :readonly="checkCustom(index)">                     
                                    </td>
                                     <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.pieces" autocomplete="off" min="0" max="100000" step="1" readonly>                     
                                    </td>
                                    <td class="input-group-sm num-sm">
                                        <input type="text" class="form-control" v-model="result.total" autocomplete="off" min="0" max="100000" step="1" readonly>                     
                                    </td>
                                    <td class="col-del"><a href="#" @click="deletePackItem(index)">x</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button id="add_new_pack_item" class="float-left btn btn-outline-warning" v-on:click="addNewLine"><i class="fas fa-plus"></i> Add SKU</a>
                <button id="clear_all_skus" class="float-left btn btn-outline-info" v-on:click="clearAllSkus"><i class="fas fa-plus"></i> Clear SKUs</a>
                <button type="button" class="btn btn-outline-danger" v-on:click="doPackDelete">Delete Pack</button>
                <button type="button" class="btn btn-warning text-danger" v-on:click="doPackSave">Save &amp; Close</button>
            </div>
        </div>
    </div>
</div>
