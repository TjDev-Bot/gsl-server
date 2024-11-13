<?= $this->extend('layouts/portal-skeleton') ?>
<?= $this->section('content') ?>
<div class="">
    <section id="shipping_list_area" class="text-start">
        <div class="gsm-bg-dark pt-4">
            <div class="container-xl text-center">
                <form>
                    <div class=" row mb-4">
                        <div class="col form-group text-left">
                            <label class="col-form-label-sm" for="search_status">Status</label>
                            <select class="form-select" id="search_status" name="search_status" v-model="filter_status" v-on:change="onFilterStatusChange">
                                <option value="0">All</option>
                                <option value="pending">Not shipped</option>
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
                            <label class="col-form-label-sm" for="search_branch">Ships To</label>
                            <select class="form-select" id="search_branch" name="search_branch" v-model="filter_branch" v-on:change="onFilterBranchChange">
                                <option value="0">All</option>
                                <?//php $locations = gsm_get_all_locations(); ?>
                                <?//php foreach( $locations as $location ) { ?>
                                    <option value="<?//php echo esc_attr( $location->slug ); ?>"><?//php echo esc_html( $location->name ); ?></option>
                                <?//php } ?>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
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

                <div class="add_new_shipment">
                    <button class="btn btn-outline-secondary text-white float-start mb-3"><i class="fas fa-plus"></i> Add New Shipment</button>
                </div>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th>Shipment #</th>
                            <th>Created</th>
                            <th>Destination</th>
                           <!-- <th>Contact</th> -->
                            
                            <th>PACKs</th>
                            <th>Included SKUs</th>
                            <th>POs</th>  
                          <!--  <th>Pieces</th>  -->
                          <!--    <th>LF</th>   -->
                            <th class="w-25">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="results.length > 0">
                            <tr v-for="result in results" v-bind:data-num="result.num">
                                <td><a href="#" v-on:click="editShipment(result.num)">{{result.title}}</a></td>
                                <td>{{result.date}}</td>
                                <td>{{result.destination}}</td>
                                <td>{{result.packs_friendly}}</td>
                                <td>{{result.included_skus}}</td>
                                <td>{{result.included_pos}}</td>
                                <td>
                                    <select class="form-select" id="shipment_status" name="shipment_status" v-model="result.shipment_status" v-on:change="updateShipmentStatus(result.num,result.shipment_status)">
                                    <?//php foreach( GSM_Packing_List::get_shipment_statuses() as $key => $value ) { ?>
                                        <option value="<?//php echo esc_attr( $key ); ?>"><?//php echo esc_html( $value ); ?></option>
                                    <?//php } ?>
                                    </select>
                                </td>  
                            </tr>
                        </template>
                        <template v-if="results.length==0">
                            <tr><td colspan="7">No Results</td></tr>
                        </template>
                    </tbody>
                </table>

                <div class="add_new_shipment">
                    <button class="btn btn-outline-secondary text-white float-start"><i class="fas fa-plus"></i> Add New Shipment</button>
                </div>
            </div>
        </div>
        <?php include( 'components/add-new-shipment.php' ); ?>
        <?php include( 'components/search-packs.php' ); ?>
    </section>
</div>

<?= $this->endSection() ?>

