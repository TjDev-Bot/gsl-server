<?= $this->extend('layouts/portal-skeleton') ?>
<?= $this->section('content') ?>
<div class="">
    <section id="packing-list-area" class="text-start">
        <div class="gsm-bg-dark pt-4">
            <div class="container-xl text-center">
                <form>
                    <div class=" row mb-4">
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_status">Pack Types</label>
                            <select class="form-select" id="pack_types" name="search_status" v-model="filter_pack_types" v-on:change="onFilterPackTypesChange">
                                <option value="0">All</option>
                                <option value="stock">Stock</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_status">Status</label>
                            <select class="form-select" id="search_status" name="search_status" v-model="filter_status" v-on:change="onFilterStatusChange">
                                <option value="0">All</option>
                                <option value="unassigned">Unassigned</option>
                                <option value="assigned">Assigned</option>
                                <option value="shipped">Shipped</option>
                            </select>
                        </div>
                        <!--
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_contains">Contains</label>
                            <select class="form-select" id="search_contains" name="search_contains" v-model="filter_contains" v-on:change="onFilterContainsChange">
                                <option value="0">All</option>
                                <?//php $skus = GSM_Packing_List::gsm_load_all_packing_skus(); ?>
                                
                                <?//php foreach( $skus as $id => $data ) { ?>
                                    <option value="<?//php echo esc_attr( $id ); ?>"><?//php echo esc_html( $data->title ); ?></option>
                                <?//php } ?>
                            </select>
                        </div>
                        -->
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_period">Created</label>
                            <select class="form-select" id="search_period" name="search_period" v-model="filter_period" v-on:change="onFilterPeriodChange">
                                <option value="0">All</option>
                                <option value="1">Last Month</option>
                                <option value="3">Last 3 Months</option>
                                <option value="6">Last 6 Months</option>
                                <option value="12">Last 12 Months</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="add-new-pak">
                    <button class="btn btn-outline-secondary text-white float-start mb-3"><i class="fas fa-plus"></i> Add New Pak</button>
                </div>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th>Pack #</th>
                            <th>Type</th>
                            <th>SKU</th>
                            <th>Description</th>
                            <th>PCS</th>  
                            <th>Length</th>  
                            <th>POs</th>  
                            <th>Assigned To</th>  
                            <th>Status</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="!results.length">
                            <tr><td colspan="6" class="text-center">No Results</td></tr>
                        </template>
                        <template v-else>
                            <tr v-for="result in results" v-bind:data-num="result.num">
                                <td><a href="#" v-on:click="editPack(result.num)">{{result.title}}<a></td>
                                <td>{{result.friendly_pack_type}}</td>

                                <td> 
                                    <div v-for="(value, key) in result.skus">
                                        {{value.sku}}
                                    </div>
                                </td>

                                <td> 
                                    <div v-for="(value, key) in result.skus">
                                        {{value.notes}}
                                    </div>
                                </td>
                                <td> 
                                    <div v-for="(value, key) in result.skus">
                                        {{value.pcs}}
                                    </div>
                                </td>
                                <td> 
                                    <div v-for="(value, key) in result.skus">
                                        {{value.length}}'
                                    </div>
                                </td>
                                <td>
                                    <div v-for="(value, key) in result.skus">
                                        {{value.po}}
                                    </div>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" :value="result.assigned_shipment" :data-num="result.num" v-model="result.assigned_shipment" :disabled="result.has_shipped == 1" v-on:change="changeAssignedShipment(result.num,result.assigned_shipment)" disabled>
                                        <option value="0">Unassigned</option>

                                        <?//php $shipments = GSM_Packing_List::get_all_shipments(); ?>
                                        <?//php foreach( $shipments as $id => $value ) { ?>
                                            <option value="<?//php echo esc_attr( $id ); ?>"<?//php if ( $value->status == 'shipped' ) echo ' disabled'; ?>><?//php echo esc_html( $value->title ); ?></option>
                                        <?//php } ?>  
                                    </select>
                                </td>
                                
                                <td class="order-status-list">
                                    <template v-if="result.assigned_shipment != 0 ">
                                        <div v-if="result.has_shipped == 1">
                                            Shipped
                                        </div>
                                        <div v-else>
                                            Not Shipped
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div>--</div>
                                    </template>
                                </td>

                                <td class="align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item duplicate-pack" data-amount="1" href="#" v-on:click="duplicatePack(result.num,1,$event)"><i class="fa-solid fa-clone"></i> Duplicate 1x</a>
                                            <a class="dropdown-item duplicate-pack" data-amount="2" href="#" v-on:click="duplicatePack(result.num,2,$event)"><i class="fa-solid fa-clone"></i> Duplicate 2x</a>
                                            <a class="dropdown-item duplicate-pack" data-amount="3" href="#" v-on:click="duplicatePack(result.num,3,$event)"><i class="fa-solid fa-clone"></i> Duplicate 3x</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item print-pack-labels" target="_blank" v-bind:href="'<?//php echo GSM_PACKINGLISTS_URL; ?>?nonce=<?//php echo wp_create_nonce( 'quote' ); ?>&gsm_action=download_pack_labels&gsm_pack_id=' + result.num"><i class="fa-solid fa-clone"></i> Print Labels</a>
                                            <a class="dropdown-item print-pack-labels" href="#" v-on:click="deletePack( result.num, $event )"><i class="fa-solid fa-clone"></i> Delete Pack</a>
                                        </div>
                                    </div>
                                </td>
                            </tr> 
                        </template>
                    </tbody>
                </table>

                <div class="add-new-pak">
                    <button class="btn btn-outline-secondary text-white float-start"><i class="fas fa-plus"></i> Add New Pak</button>
                </div>
            </div>
        </div>
        <?php include( 'components/add-new-pack.php' ); ?>
    </section>
</div>		


<?= $this->endSection() ?>


