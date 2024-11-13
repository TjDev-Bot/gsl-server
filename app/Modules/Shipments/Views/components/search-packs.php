<div class="modal fade" id="search_packs" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered text-left" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalCenterTitle">Adding Packs To Shipment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col form-group text-left">
                        <label class="col-form-label-sm" for="pack_types">Pack Types</label>
                        <select class="form-select" id="pack_types" name="search_status" v-model="search_packs_filter" v-on:change="updateSearchPackDialog">
                            <option value="stock">Stock</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col form-group text-left" v-if="search_packs_filter=='stock'">
                        <label class="col-form-label-sm" for="po_to_assign">PO To Assign</label>
                        <input type="text" class="form-control" id="po_to_assign" name="search_status" v-model="po_to_assign" autocomplete="off">                     
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <table class="table table-sm table-bordered pack-search-table" >
                            <thead>
                                <tr class="bg-primary  text-white text-center">
                                    <th>Select</th>
                                    <th>Pack #</th>
                                    <th>SKU</th>
                                    <th v-if="search_packs_filter=='custom'">PO</th>
                                    <th>6'</th>
                                    <th>7'</th>
                                    <th>8'</th>
                                    <th>9'</th>
                                    <th>10'</th>
                                    <th>11'</th>
                                    <th>12'</th>
                                    <th>13'</th>
                                    <th>14'</th>
                                    <th>15'</th>
                                    <th>16'</th>
                                    <th>PCs</th>
                                    <th>LF</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="result in search_packs_results">
                                    <tr v-for="(item, i_sub) in result.items" class="search_packs_row text-center">
                                        <td class="selected" v-if="i_sub==0" :rowspan="result.items.length" :data-num="result.num" ><input type="checkbox" class="form-check-input" autocomplete="off" />
                                        <td v-if="i_sub==0" :rowspan="result.items.length">{{result.title}}</td>
                                        <td>{{item.packed_sku}}</td>
                                        <td v-if="search_packs_filter=='custom'">{{item.po}}</td>
                                        <td>{{item.length_6}}</td>
                                        <td>{{item.length_7}}</td>
                                        <td>{{item.length_8}}</td>
                                        <td>{{item.length_9}}</td>
                                        <td>{{item.length_10}}</td>
                                        <td>{{item.length_11}}</td>
                                        <td>{{item.length_12}}</td>
                                        <td>{{item.length_13}}</td>
                                        <td>{{item.length_14}}</td>
                                        <td>{{item.length_15}}</td>
                                        <td>{{item.length_16}}</td>
                                        <td>{{item.total_pcs}}</td>
                                        <td>{{item.total_length}}'</td>
                                    </tr>
                                </tr>
                                </template>
                                
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <template v-if="!po_to_assign && search_packs_filter=='stock'">
                    <button id="add_new_pack_item" class="float-left btn btn-outline-warning" v-on:click="addSelectedPack" disabled><i class="fas fa-plus"></i> Add Selected Pack</button>
                </template>
                <template v-else>
                    <button id="add_new_pack_item" class="float-left btn btn-outline-warning" v-on:click="addSelectedPack"><i class="fas fa-plus"></i> Add Selected Pack</button>       
                </template>
                <button type="button" class="btn btn-secondary" v-on:click="closeShipmentModal">Close</button>
            </div>
        </div>
    </div>
</div>
