<?// Functions
$quote_id = (int)$_GET[ 'gsm_quote_id' ];

?>
<?= $this->extend('layouts/portal-skeleton') ?>
<?= $this->section('content') ?>



<?= $this->endSection() ?>



?>

<div class="quote-area-wrap">
    <section id="quote-area" class="text-start new-page">
        <div class="gsm-bg-dark pt-4">
            <div class="container-lg text-center">
                <?//php $user = wp_get_current_user(); ?>

                <?//php $quote_data = gsm_load_quote_data(); ?>
                <?//php 
                    //print_r( $quote_data ); die; 
                    ?>

                <?php if ( $quote_id ) { ?>
                    <h4 class="page-title"><?php echo gsm_get_quote_or_order( $quote_data ); ?> <?php echo esc_html( $quote_id ); ?></h4>
                    <?php if ( $quote_data->is_order == 0 ) { ?>
                        <div class="pricing-from"><em><?php echo date( 'F j, Y', $quote_data->active_pricing->date ); ?></em></div>
                    <?php } else { ?>
                        <div class="pricing-from"><em><?php echo get_the_time( 'F j, Y', $quote_id ); ?></em></div>
                    <?php } ?>
                    
                    <div class="gsm-button-row">
                        <?php if ( $quote_data->is_order == 0 ) { ?>
                            <a class="btn btn-outline-warning quick-quote" href="#"><i class="fas fa-window-restore"></i> Quick Quote X</a>
                            <button class="btn btn-outline-info fp-sku-lookup" data-show-add="1"><i class="fas fa-search"></i> Search Drawings X</button>
                        <?php } ?>

                        <div class="dropdown" style="display: inline-block">
                            <button class="btn btn-outline-danger dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 100%">
                                <i class="fa-solid fa-wrench"></i> Actions
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <?php if ( $quote_data->is_order ) { ?>
                                    <a class="convert-order dropdown-item" href="<?php echo add_query_arg( array( 'gsm_action' => 'convert_to', 'convert_type' => 'quote', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fa-solid fa-arrow-rotate-right"></i> Revert to Quote</a>
                                    <a class="dropdown-item duplicate-quote" href="<?php echo add_query_arg( array( 'gsm_action' => 'duplicate_quote', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fa-solid fa-clone"></i> Duplicate Order</a>
                                <?php } else { ?>
                                    <a class="convert-order dropdown-item" href="<?php echo add_query_arg( array( 'gsm_action' => 'convert_to', 'send_emails' => '1', 'convert_type' => 'order', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fa-solid fa-arrow-rotate-right"></i> Convert to Order</a>
                                    <a class="dropdown-item duplicate-quote" href="<?php echo add_query_arg( array( 'gsm_action' => 'duplicate_quote', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fa-solid fa-clone"></i> Duplicate Quote</a>
                                <?php } ?>

                                <button class="dropdown-item view-quote-log" data-quote-id="<?php echo esc_attr( $quote_id ); ?>" href="#"><i class="fas fa-book"></i> View Update History</button>
                                <a class="dropdown-item update-pricing" href="#" title="Pricing from <?php echo esc_attr( date( 'm/d/Y g:ia', $quote_data->active_pricing->date ) ); ?>"><i class="fas fa-dollar-sign"></i> Update Pricing</a>
                                <a class="dropdown-item delete-quote" href="<?php echo add_query_arg( array( 'gsm_action' => 'delete_quote', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fas fa-trash"></i> Delete</a>						
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                <!-- New Quote Page -->
                    <h4 class="page-title">New Quotation X</h4>
                    <div class="gsm-button-row">
                        <a class="btn btn-outline-warning quick-quote" href="#"><i class="fas fa-window-restore"></i> Quick Quote X</a>
                        <button class="btn btn-outline-info fp-sku-lookup" data-show-add="1"><i class="fas fa-search"></i> Search Drawings X</button>
                    </div>
                <?php } ?>

                <input type="hidden" name="gsm_is_order" id="gsm_is_order" value="<?php echo esc_attr( $quote_data->is_order ); ?>" />
                <?php $species = $quote_data->active_pricing->species; ?>
                <?php $species_base = gsm_get_all_species_base( $quote_data->active_pricing->species ); ?>
            </div>
            <div class="top-area-wrap">
                <div class="container-lg mb-2">
                    <?php $quote_age_in_days = ( time() - $quote_data->active_pricing->date ) / (3600*24.0); ?>
                    <?php if ( !$quote_data->is_order && ( $quote_age_in_days >= 30 ) ) { ?>
                    <div class="alert text-white mt-4 text-center" role="alert" style="background: darkred;">
                        <strong>The pricing in this quotatation is out-of-date. <br />You should consider updating the pricing using the button above and then re-adjust any fixed cost items afterwards.</strong>
                    </div>
                    <?php } ?>

                    <div class="top-area">
                        <div class="row mb-4">
                            <div class="col-md text-left mt-3">
                                <?php include( 'components/quotes/info-customer.php' ); ?>
                            </div>
                            <div class="col-md text-left mt-3">
                                <div class="shipment-info">
                                    <h6 class="text-center gsm-border-title">Shipment Information</h6>
                                    <?php include( 'components/quotes/info-shipment.php' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-lg">
            <form>
                <div class="table-responsive">
                    <table id="cost_table" class="table table-bordered table-sm">
                        <thead>
                            <tr class="table-dark text-center">
                                <th class="enable-disable">&nbsp;</th>
                                <th class="qty">Qty X</th>
                                <th>UM</th>
                                <th class="sku-col">SKU</th>
                                <th>Description</th>
                                <th>$/UM</th>

                                <th class="price-col">Total Cost</th>
                                <th class="action-col">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="dummy" style="display: none;" data-custom-behavior="default" data-is-active="1">
                                <td class="enable-disable align-middle text-center handle">
                                    <i class="fa fa-arrows" aria-hidden="true"></i>
                                    <div class="form-check form-switch text-center">
                                        <input class="form-check-input flip-active" type="checkbox" role="switch" checked autocomplete="no">
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <input class="form-control qty" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="1" name="qty" id="qty" autocomplete="off" />
                                </td>

                                <td class="align-middle text-center um">
                                    <div>LF</div>
                                    <?php /*?><br /><?php */?><button type="button" class="btn btn-light open-um mt-1 mb-1"><i class="fas fa-pen"></i> Set</button>
                                </td>

                                <td class="align-middle" class="sku-col">
                                    <?php include( 'components/quotes/skus-add-new.php' ); ?>
                                </td>

                                <td class="align-middle text-center">
                                    <div class="info">
                                        <div class="desc"></div>
                                        <div class="wood"></div>
                                        <div class="finish"></div>
                                        <div class="notes"></div>
                                    </div>
                                    <button type="button" class="btn btn-light options mt-1 mb-1" data-toggle="modal" disabled><i class="fas fa-cog"></i> Options</button>
                                    <a href="" style="display: none;" type="button" class="btn btn-light options mt-1 mb-1 mill-drawing" target="_blank"><i class="fa-solid fa-ruler-combined"></i> Mill Drawing</a>
                                </td>
                                <td class="align-middle text-center price-per-um">
                                    <div class="price-per-lf monospace">$0.00</div>
                                    <button type="button" class="btn btn-light overrides mt-1 mb-1" disabled><i class="fas fa-pen"></i> Overrides</button>
                                </td>

                                <td class="align-middle text-center price-col monospace">
                                    <div class="price">$0.00</div>
                                </td>
                                <td class="align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item duplicate-item" href="#"><i class="fa-solid fa-clone"></i> Duplicate</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item delete-item" href="#"><i class="fa-solid fa-trash-can"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <?php if ( isset( $quote_data->items ) && is_countable( $quote_data->items ) && count( $quote_data->items ) ) { ?>
                                <script type="text/javascript">var gsmUpdatePricing = 1;</script>
                                <?php $num = 1; ?>
                                <?php foreach( $quote_data->items as $item ) { ?>
                                    <?php
                                       // print_r( $item ); die;

                                        if ( $item[ 'sku' ] == 'misc' ) {
                                            $tr_class = 'is-freeform' ;
                                        } else if ( $item[ 'sku' ] == 'custom' || $item[ 'sku' ] == 's4s' ) {
                                            $tr_class = 'is-custom' ;
                                        } else {
                                            $tr_class = 'is-normal-sku' ;
                                        }

                                        $tr_string = 'class="' . $tr_class . ' not-dummy" ';

                                        if ( ( $item[ 'sku' ] == 'custom' || $item[ 'sku' ] == 's4s' ) && $item[ 'species' ] ) {
                                            $some_species = $item[ 'species' ];
                                            if ( $some_species == 'custom' ) {
                                                $some_species = 'poplar';
                                            }

                                            $our_species = $species_base[ $some_species ];
                                           // print_r( $our_species ); die;
                                            $tr_string .= 'data-custom-thicknesses="' . implode( ',', $our_species->thicknesses ) . '" ';

                                            foreach ( $our_species->thicknesses as $thick ) {
                                                $tr_string .= 'data-custom-thickness-price-' . $thick . '="' . $our_species->marked_up_yielded_costs[ $thick ] . '" ';
                                            }
                                        }
                                        //print_r( $item[ 'moulding_info' ] );

                                        $tr_string .= 'data-num="' . $num++ . '" ';
                                        $tr_string .= 'data-profile="' . $item[ 'moulding_info' ]->profile . '" ';
                                        $tr_string .= 'data-width="' . ( isset( $item[ 'moulding_info' ]->friendly_width ) ? $item[ 'moulding_info' ]->friendly_width  : '' ) . '" ';
                                        $tr_string .= 'data-real-width="' . ( isset( $item[ 'moulding_info' ]->width ) ? $item[ 'moulding_info' ]->width : '' ) . '" ';
                                        $tr_string .= 'data-thickness="' . ( isset( $item[ 'moulding_info' ]->friendly_thickness ) ? $item[ 'moulding_info' ]->friendly_thickness : '' ) . '" ';
                                        $tr_string .= 'data-markup="' . gsm_get_default_markup( $item[ 'markup' ] ) . '" ';
                                        $tr_string .= 'data-gsm-adjust="' . ( isset( $item[ 'gsm_adjust' ] ) ? $item[ 'gsm_adjust' ] : '1.00' ) . '" ';
                                        $tr_string .= 'data-finish="' . $item[ 'finish' ] . '" ';
                                        $tr_string .= 'data-species="' . $item[ 'species' ] . '" ';
                                        $tr_string .= 'data-species-custom="' . $item[ 'species_custom' ] . '" ';
                                        $tr_string .= 'data-notes="' . esc_attr( $item[ 'notes' ] ) . '" ';
                                        $tr_string .= 'data-custom-desc="' . esc_attr( $item[ 'custom_desc' ] ) . '" ';
                                        $tr_string .= 'data-custom-sku="' . esc_attr( $item[ 'custom_sku' ] ) . '" '; 
                                        $tr_string .= 'data-ripsku="' . ( isset( $item[ 'moulding_info' ]->ripsku ) ? $item[ 'moulding_info' ]->ripsku : '' ) . '" ';
                                        $tr_string .= 'data-unit-measure="' . ( isset( $item[ 'unit_of_measure' ] ) ? $item[ 'unit_of_measure' ] : 1 ). '" ';
                                        $tr_string .= 'data-pc-length="' . ( isset( $item[ 'unit_pc_length' ] ) ? $item[ 'unit_pc_length' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-thickness="' . ( isset( $item[ 'custom_thickness' ] ) ? $item[ 'custom_thickness' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-width="' . ( isset( $item[ 'custom_width' ] ) ? $item[ 'custom_width' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-thickness-friendly="' . ( isset( $item[ 'custom_thickness_friendly' ] ) ? $item[ 'custom_thickness_friendly' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-width-friendly="' . ( isset( $item[ 'custom_width_friendly' ] ) ? $item[ 'custom_width_friendly' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-ripsku="' . ( isset( $item[ 'custom_ripsku' ] ) ? $item[ 'custom_ripsku' ] : 0 ). '" ';
                                        $tr_string .= 'data-custom-behavior="' . ( isset( $item[ 'custom_behavior' ] ) ? $item[ 'custom_behavior' ] : 'default' ). '" ';
                                        $tr_string .= 'data-edge-glue="' . ( isset( $item[ 'edge_glue' ] ) ? $item[ 'edge_glue' ] : '0' ). '" ';
                                        $tr_string .= 'data-include-setup-charge="' . ( isset( $item[ 'include_setup_charge' ] ) ? $item[ 'include_setup_charge' ] : '1' ). '" ';
                                        $tr_string .= 'data-knife-charge="' . ( isset( $item[ 'knife_charge' ] ) ? $item[ 'knife_charge' ] : '0' ). '" ';
                                        $tr_string .= 'data-override-charge="' . ( isset( $item[ 'override_charge' ] ) ? $item[ 'override_charge' ] : '' ). '" ';
                                        $tr_string .= 'data-override-gsm="' . ( isset( $item[ 'override_gsm' ] ) ? $item[ 'override_gsm' ] : '' ). '" ';
                                        $tr_string .= 'data-override-finish="' . ( isset( $item[ 'override_finish' ] ) ? $item[ 'override_finish' ] : '' ). '" ';
                                        $tr_string .= 'data-override-edge="' . ( isset( $item[ 'override_edge' ] ) ? $item[ 'override_edge' ] : '' ). '" ';
                                        $tr_string .= 'data-override-setup="' . ( isset( $item[ 'override_setup' ] ) ? $item[ 'override_setup' ] : '' ). '" ';
                                        $tr_string .= 'data-override-knife="' . ( isset( $item[ 'override_knife' ] ) ? $item[ 'override_knife' ] : '' ). '" ';
                                        $tr_string .= 'data-drawing-url="' . ( isset( $item[ 'drawing_url' ] ) ? $item[ 'drawing_url' ] : '' ). '" ';
                                        $tr_string .= 'data-drawing-name="' . ( isset( $item[ 'drawing_name' ] ) ? $item[ 'drawing_name' ] : '' ). '" ';
                                        $tr_string .= 'data-setup-charge-type="' . ( isset( $item[ 'setup_charge_type' ] ) ? $item[ 'setup_charge_type' ] : 'normal' ). '" ';

                                    
                                        $checked = false;
                                        if ( isset( $item[ 'is_active' ] ) ) {
                                            $checked = ( $item[ 'is_active' ] == '1' );
                                        } else {
                                            $checked = !isset( $item[ 'is_active' ] );
                                        }

                                        $tr_string .= 'data-is-active="' . ( $checked ? '1' : '0' ). '" ';

                                       // echo $item[ 'moulding_info' ]->mill_drawing; die;

                                        if ( isset( $item[ 'moulding_info' ] ) && isset( $item[ 'moulding_info' ]->mill_drawing ) ) {
                                       // print_r( $item ); die;
                                            $tr_string .= 'data-mill-drawing="' . esc_url( $item[ 'moulding_info' ]->mill_drawing[ 'url' ] ) . '" '; 
                                        } else {
                                            $tr_string .= 'data-mill-drawing="" ';
                                        }

                                        $all_species = array();

                                        if ( isset( $item[ 'moulding_info' ]->pricing )  ) {
                                            foreach( $item[ 'moulding_info' ]->pricing as $wood => $price ) {
                                                $tr_string .= 'data-species-price-' . gsm_cleanup_wood_slug( $wood ) . '="' . sprintf( '%0.5f', $price ) . '" ';
                                                $all_species[] = gsm_cleanup_wood_slug( $wood );
                                            }
                                        } else {
                                            // for custom or S4S
                                            $all_species = gsm_get_all_species_raw( $quote_data->active_pricing->species );
                                        }

                                        $tr_string .= 'data-all-species="' . implode( ' ', $all_species ) . '"';

                                    ?>
                                    <tr <?php echo $tr_string; ?>>
                                        <td class="enable-disable align-middle text-center handle">
                                            <i class="fa fa-arrows" aria-hidden="true"></i>
                                            <div class="form-check form-switch text-center">
                                                <input class="form-check-input flip-active" type="checkbox" role="switch" autocomplete="no" <?php if ( $checked ) echo 'checked'; ?>>
                                            </div>
                                          
                                        </td>
                                        <td class="align-middle" style="width: 80px">
                                            <input class="form-control qty" type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="<?php echo esc_attr( $item[ 'quantity' ] ); ?>" name="qty" id="qty" autocomplete="off" <?php if ( $item[ 'sku' ] == 'freeform' ) echo 'disabled'; ?> />
                                        </td>
                                        <td class="align-middle text-center um">
                                            <div></div>
                                            <?php /*?><br /><?php */?><button type="button" class="btn btn-light open-um mt-1 mb-1"><i class="fas fa-pen"></i> Set</button>
                                        </td>

                                        <td class="align-middle" class="sku-col">
                                            <?php include( 'components/quotes/skus.php' ); ?>
                                        </td>

                                        <td class="align-middle text-center">
                                            <div class="info">
                                                <div class="desc"></div>
                                                <div class="wood"></div>
                                                <div class="finish"></div>
                                                <div class="notes"></div>                                                
                                            </div>
                                            <button type="button" class="btn btn-light options mt-1 mb-1" data-toggle="modal"><i class="fas fa-cog"></i> Options</button>
                                            <a href="" style="display: none;" type="button" class="btn btn-light options mt-1 mb-1 mill-drawing" target="_blank"><i class="fa-solid fa-ruler-combined"></i> Mill Drawing</a>
                                        </td>

                                        <td class="align-middle text-center price-per-um">
                                            <div class="price-per-lf monospace">$0.00</div>
                                            <?php /*?><br /><?php */?>
                                            <button type="button" class="btn btn-light mt-1 mb-1 overrides"<?php if ( !$item[ 'species' ] ) { echo ' disabled'; } ?>><i class="fas fa-pen"></i> Overrides</button>
                                        </td>

                                        <td class="align-middle text-center price-col">
                                            <div class="price monospace">$0.00</div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item duplicate-item" href="#"><i class="fa-solid fa-clone"></i> Duplicate</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item delete-item" href="#"><i class="fa-solid fa-trash-can"></i> Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <script type="text/javascript">var gsmUpdatePricing = 0;</script>
                            <?php } ?>
                        </tbody>    
                        <tfoot>
                            <tr class="subtotal text-center">
                                <td colspan="6" class="text-end"><strong>Subtotal</strong></td>
                                <td class="price-col"><div class="price monospace">$0.00</div></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="freight text-center">
                                <td colspan="6" class="text-end"><strong>Freight</strong></td>
                                <td class="price-col"><div class="price monospace">$0.00</div></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="tax text-center">
                                <td colspan="6" class="text-end"><strong>Tax</strong></td>
                                <td class="price-col"><div class="price monospace">$0.00</div></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="total text-center">
                                <td colspan="6" class="text-end"><strong>Total</strong></td>
                                <td class="price-col"><div class="price monospace">$0.00</div></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-left">
                    <a href="#" id="add_new_item" class="btn btn-outline-secondary text-white"><i class="fas fa-plus"></i> Add Item</a>
                    <?php if ( $quote_data->is_order == 0 ) { ?>
                        <button class="btn btn-warning save-quote"<?php if ( $quote_id == 0 ) echo ' disabled'; ?>><i class="fas fa-save"></i> Save Quote</button>
                    <?php } else { ?>
                        <button class="btn btn-warning save-quote"<?php if ( $quote_id == 0 ) echo ' disabled'; ?>><i class="fas fa-save"></i> Update Order</button>
                    <?php } ?>
                </div>

                <div class="row mt-4 quote-notes-area">
                    <div class="col col-6">
                        <label for="customer_notes">Customer Notes</label>
                        <textarea class="form-control" id="customer_notes" name="customer_notes" placeholder="Only the customer will see these notes..." autocomplete="off"><?php echo esc_html( $quote_data->customer_notes ); ?></textarea>
                    </div>
                    <div class="col col-6">
                        <label for="mill_notes">Mill Notes</label>
                        <textarea class="form-control" id="mill_notes" name="mill_notes" placeholder="Only the mill will see these notes..." autocomplete="off"><?php echo esc_html( $quote_data->mill_notes ); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <div class="container-lg pb-5">
        <div class="row mt-4">
    
            <div class="col text-left bottom">
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"  aria-haspopup="true" aria-expanded="false"  style="width: 100%">
                        <i class="fa-solid fa-person"></i> Customer
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="width: 100%">
                        <a class="dropdown-item send-email" href="#"><i class="far fa-envelope"></i> Send Email</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item download-pdf" target="_blank" href="<?php echo gsm_get_public_pdf_string( $quote_id ); ?>" download onclick="return confirm( 'Please make sure you save the quote before generating a PDF' );"><i class="fa-solid fa-download"></i> Download PDF</a>
                    </div>
                </div>
            </div>

            <div class="col text-left bottom">
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 100%">
                        <i class="icon-saw"></i> Mill
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"  style="width: 100%">
                        <a class="dropdown-item send-mill-email" href="#"><i class="far fa-envelope"></i> Send Email</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item download-mill-pdf" target="_blank" href="<?php echo gsm_get_public_pdf_string( $quote_id, 'mill' ); ?>" download onclick="return confirm( 'Please make sure you save the quote before generating a PDF' ); "><i class="fa-solid fa-download"></i> Download PDF</a>
                        <a class="dropdown-item download-mill-labels" target="_blank" href="<?php echo add_query_arg( array( 'gsm_action' => 'download_mill_labels', 'nonce' => wp_create_nonce( 'quote' ) ) ); ?>"><i class="fa-solid fa-download"></i> Download Labels</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-lg pb-5" <?php if ( !GSM_DEBUG_ON ) echo ' style="display: none;"'?>>
        <div class="text-left bottom" id="debug=area">
            <textarea id="debug_window" rows="20" autocomplete="off" style="width: 100%" value=""></textarea>
        </div>
    </div>
</div>

<div id="autocomplete-area" style="position: absolute"></div>

<?php include( 'components/shared/quick-quote.php' ); ?>
<?php include( 'components/shared/drawing-search.php' ); ?>

<?php include( 'components/quotes/customer-lookup.php' ); ?>
<?php include( 'components/quotes/quote-log.php' ); ?>
<?php include( 'components/quotes/unit-measure.php' ); ?>
<?php include( 'components/quotes/email.php' ); ?>
<?php include( 'components/quotes/dual-email.php' ); ?>

<div id="options_override_vue">
	<?php include( 'components/quotes/options.php' ); ?>
	<?php include( 'components/quotes/overrides.php' ); ?>
</div>

