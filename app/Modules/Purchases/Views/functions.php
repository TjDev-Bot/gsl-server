<?php
/* WP 
include 'debug-log.php';
include 'includes/order-status.php';
include 'includes/packing-list.php';
include 'includes/pricing.php';
include 'includes/labels.php';
include 'includes/pdf.php';
include 'includes/overstock.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
*/
define( 'GSM_THEME_VER', '2.0.1' );
define( 'GSM_MARKUP_VALUES', array( 1, 1.05, 1.10, 1.12, 1.15, 1.18, 1.20, 1.22, 1.25, 1.30, 1.35, 1.40, 1.45, 1.50, 1.55, 1.60, 1.65, 1.70, 1.75, 1.80, 1.85, 1.90, 2.0, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 3.0 ) );
define( 'GSM_QUOTE_URL', '/orders/' );
define( 'GSM_PACKINGLISTS_URL', '/packing-lists/' );
/* WP
add_action( 'wp', 'gsm_wp' );
add_action( 'init', 'gsm_init' );
add_action( 'wp_ajax_handle_ajax', 'gsm_handle_ajax' );
add_action( 'wp_ajax_nopriv_handle_ajax', 'gsm_handle_ajax' );
add_action( 'wp_enqueue_scripts', 'gsm_enqueue_scripts' );

//error_reporting(0);

$gsm_log_file;

function gsm_does_user_have_role(  $role, $user_id = false ) {
    if ( !$user_id ) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata( $user_id );
    if ( $user && isset( $user->roles ) ) {
        return in_array( $role, $user->roles );
    }

    return false;
}

function gsm_does_author_have_any_posts( $user_id) {
    $args = array(
        'post_type'  => 'gsm_quotations',
        'author'     => $user_id,
    );
    
    $wp_posts = get_posts( $args );
    
    return ( count( $wp_posts ) );
}
*/
function gsm_get_quote_or_order( $quote_data ) {
    if ( $quote_data->is_order ) {
        return 'Order';
    } else {
        return 'Quotation';
    }
}

function gsm_get_all_companies() {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT DISTINCT(meta_value) AS name FROM " . $wpdb->prefix . "postmeta WHERE meta_key=%s AND meta_value <> '' ORDER BY meta_value ASC", "customer_name" );
    $results = $wpdb->get_results( $sql );

    return $results;
}

function gsm_add_custom_post_types() {
    register_post_type( 
        'gsm_quotations',
        array(
            'label' => 'GSM Quotations',
            'labels' => array(
                'add_new_item' => 'Add New Quotation',
                'edit_item' => 'Edit Quotation'
            ),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => array(
                'title',
                'author',
                'custom-fields'
            )
        )
    );

    register_post_type( 
        'gsm_locations',
        array(
            'label' => 'GSM Locations',
            'labels' => array(
                'add_new_item' => 'Add New Location',
                'edit_item' => 'Edit Location'
            ),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'rewrite' => true,
            'show_ui' => true,
            'menu_icon' => 'dashicons-admin-site',
            'supports' => array(
                'title',
                'custom-fields'
            ),
            'public' => true
        )
    );
}

function gsm_get_all_locations() {
    $query = new WP_Query(
        array(
            'post_type' => 'gsm_locations',
            'post_status' => 'publish'
        )
    );

    $locations = array();

    while ( $query->have_posts() ) {
        $query->the_post();

        $location = new stdClass;
        $location->slug = strtoupper( basename( get_permalink() ) );
        $location->name = get_the_title();
        $location->address = get_field( 'address' );

        $locations[ $location->slug ] = $location;
    }

    return $locations;
}

function gsm_init() {
    // Checek login/logout
    $lost_password = ( strpos( $_SERVER[ 'REQUEST_URI' ], 'lostpassword' ) !== false ); 
    if ( strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-login.php' ) !== false ) {
        $logging_out = isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'logout';
        if ( strpos( $_SERVER[ 'REQUEST_URI' ], 'redirect_to=' ) === false && $_SERVER[ 'REQUEST_METHOD' ] == 'GET' ) {
            if ( !$lost_password && !$logging_out ) {
                $new_location = wp_login_url( '/' );
                header( 'Location: ' . $new_location ); die;
            } 
        }
    }

    gsm_update_remote_images();

    global $gsm_log_file;
    $gsm_log_file = new DebugLog;

    gsm_add_custom_post_types();

    if ( function_exists( 'acf_add_options_page' ) ) {
        acf_add_options_page( 
            array(
                'page_title' 	=> 'Order Emails',
                'menu_title'	=> 'Order Emails',
                'menu_slug' 	=> 'gsm-order-emails',
                'capability'	=> 'manage_options',
                'redirect'		=> false
            )
        );
    }

    if ( isset( $_GET[ 'gsm_pdf' ] ) ) {
        if ( $_GET[ 'gsm_pdf' ] == 'customer' || $_GET[ 'gsm_pdf' ] == 'mill' ) {
            $quote_id = $_GET[ 'gsm_quote_id' ];
            if ( $quote_id ) {
                $author_id = 0;
                if ( is_user_logged_in() ) {
                    $author_id = get_current_user_id();
                }

                $can_download = false;

                if ( isset( $_GET[ 'auth' ] ) && isset( $_GET[ 'data' ] ) ) {
                    $auth = $_GET[ 'auth' ];
                    $data = $_GET[ 'data' ];

                    $result = gsm_pdf_can_download( $quote_id, base64_decode( $data ), $auth );
                    if ( $result ) {
                        $author_id = 0;
                        if ( is_user_logged_in() ) {
                            $author_id = get_current_user_id();
                        }
                        
                        switch( $result ) {
                            case 'customer':
                                 $mpdf = gsm_get_mpdf_for_quote( $quote_id, false, $author_id );
                                $mpdf->Output( 'GSL-Quote-' . $quote_id . '.pdf', 'D' );
                                break;
                            case 'mill':
                                $mpdf = gsm_get_mpdf_for_quote( $quote_id, true, $author_id );
                                $mpdf->Output( 'GSM-Quote-' . $quote_id . '.pdf', 'D' );
                                break;
                        }

                        die;
                    } else {
                        echo 'Invalid PDF Download Link';
                    }
                }
            }
        }
    }

    if ( isset( $_GET[ 'gsm_action' ] ) ) {
        // make sure logged out users can't add anything
        if ( !is_user_logged_in() ) {
            header( 'Location: /' );
            die;
        }

        $new_args = array( 'gsm_action' => false, 'nonce' => false, 'gsm_pack_id' => false );

        $nonce = $_GET[ 'nonce' ];
        $new_loc = false;

        if ( wp_verify_nonce( $nonce, 'quote' ) ) {   
            switch( strtolower( $_GET[ 'gsm_action' ] ) ) {
                case 'convert_to':
                    $quote_id = $_GET[ 'gsm_quote_id' ];
                    $convert_to = $_GET[ 'convert_type' ];

                    if ( $quote_id ) {
                        switch( $convert_to ) {
                            case 'quote':
                                update_post_meta( $quote_id, 'is_order', 0 );

                                gsm_update_save_data( $quote_id, 'Converted into QUOTE' );
                                break;
                            case 'order':
                                update_post_meta( $quote_id, 'is_order', 1 );
                                gsm_update_quote_to_current_time( $quote_id );

                                gsm_update_save_data( $quote_id, 'Converted into ORDER' );
                                break;
                        }
                    }

                    $new_args[ 'convert_type' ] =  false;
                    break;
                case 'add_new':
                    /*
                    $post_params = array(
                        'post_type' => 'gsm_quotations',
                        'post_status' => 'publish'
                    );

                    $post_id = wp_insert_post( $post_params );
                    $new_args[ 'gsm_quote_id' ] =  $post_id;
                    */
                    $new_args[ 'gsm_quote_id' ] =  0;

                    break;
                case 'download_mill_labels':
                    $quote_id = $_GET[ 'gsm_quote_id' ];
                    if ( $quote_id ) {
                        $author_id = get_current_user_id();

                        $mpdf = GSM_Mill_Labels::generate_labels( $quote_id );
                        $mpdf->Output( 'GSM-Labels-' . $quote_id . '.pdf', 'D' );
                    }
                    die;

                    break;    
                case 'download_pack_labels':
                    $pack_id = $_GET[ 'gsm_pack_id' ];
                    if ( $pack_id ) {
                        $mpdf = GSL_Pack_Labels::generate_labels( $pack_id );
                        $mpdf->Output( 'GSM-Pack-' . $pack_id . '.pdf', 'D' );
                    }
                    die;
                    
                    break;
                case 'delete_quote':
                    $quote_id = $_GET[ 'gsm_quote_id' ];

                    if ( is_numeric( $quote_id ) ) {
                        // TODO: check permissions
                        wp_delete_post( $quote_id );
                    }

                    $new_args[ 'gsm_quote_id' ] = false;
                    $new_loc = '/';
                    break;
            }
        }

        if ( $new_loc ) {
            header( 'Location: ' . add_query_arg( $new_args, $new_loc ) );
        } else {
            header( 'Location: ' . add_query_arg( $new_args ) );
        }
        
        die;
    } 

    // Set up packing list
    global $gsm_packing_list;
    $gsm_packing_list = new GSM_Packing_List;
    $gsm_packing_list->init();

    global $gsm_overstock;
    $gsm_overstock = new GSM_Overstock;
    $gsm_overstock->init();

}

function gsm_packing_list() {
    global $gsm_packing_list;
    return $gsm_packing_list;
}

function gsm_spaces_to_dashes( $str ) {
    return str_replace( ' ', '-', $str );
}

function gsm_cleanup_sku( $sku ) {
    return strtoupper( $sku );
}

function gsm_get_image_object_case_insensitive( $obj, $name ) {
    $properties = array_keys( get_object_vars( $obj ) );
    foreach( $properties as $one_name ) {
        if ( strcasecmp( $name, $one_name ) == 0 ) {
            return $obj->$one_name;
        }     
    }

    return NULL;
}

function gsm_get_image_for_sku( $sku ) {
    $sku = gsm_cleanup_sku( $sku );

    $image_info = get_option( 'gsm_images_data', false );
    if ( $image_info ) {
        $post_id = gsm_get_id_from_sku( $sku );

        GSM_DEBUG( __FUNCTION__ . ' Post ID for SKU is ' . $post_id );

        if ( $post_id ) {
            $override_image = get_field( 'thumbnail_image', $post_id );
            if ( $override_image ) {
               return $override_image[ 'sizes' ][ 'medium' ];
            } else {
                $image_obj = gsm_get_image_object_case_insensitive( $image_info, $sku );
                if ( $image_obj ) {
                    if ( isset( $image_obj->image ) ) {

                        return $image_obj->image;
                    } else if ( $image_obj->rendered_image ) {
                        return $image_obj->rendered_image;
                    }
                }
            }
        } else {
            // S4s or CUSTOM
            $base_url = get_bloginfo( 'template_url' );

            if ( $sku == 'CUSTOM' ) {
                $image = $base_url . '/images/custom-image.png';
            } else if ( $sku == 'S4S' ) {
                $image = $base_url . '/images/S4S.png';
            }

            GSM_DEBUG( __FUNCTION__ . ' => ' . $sku . ' ' . $post_id . ' ' . $image );
            return $image;
        }

    }

    return false;
}

function gsm_get_id_from_sku( $sku ) {
    
    $query = new WP_Query(
        array(
            'title' => $sku,
            'post_status' => 'publish',
            'post_type' => array( 'gsm-mouldings', 'gsm-custom-mouldings' )
        )
    );

    $id = 0;
    if ( $query->have_posts() ) {
        $query->the_post();

        $id = get_the_ID();
        GSM_DEBUG( __FUNCTION__ . ' => ' . $id . ' => ' . $sku );
    } 

    return $id;
}


function gsm_update_remote_images() {
    $api = 'https://api.mouldingmodule.com/wp-json/mouldings/v1/profiles/images/';

    $last_update_time = get_option( 'gsm_images_update_time', 0 );
    $interval = time() - $last_update_time;

    if ( $interval > 3600 ) {
        $response = wp_remote_get( $api );

        if ( is_array( $response ) && ! is_wp_error( $response ) ) {
            $body = $response[ 'body' ];
            $decoded_response = json_decode( $body );

            update_option( 'gsm_images_data', $decoded_response->profiles, false );
            update_option( 'gsm_images_update_time', time() );
        }
    }
}

function gsm_get_one_configuration( $option ) {
    $config = gsm_get_configuration();

    return $config->$option;
}

function gsm_get_global_tiers() {
    return get_field( 'global_markup_tiers', 'options' );
}

function gsm_get_configuration() {
    $config = new stdClass;

    $config->tax_rate = (float)get_field( 'quote_general_tax_rate', 'option' );
    $config->default_lead_time = (float)get_field( 'quote_general_lead_time', 'option' );

    $config->knife_charge = (float)get_field( 'quote_charges_knife_charge', 'option' );
    $config->custom_setup_charge = (float)get_field( 'quote_charges_setup_charge', 'option' );
    $config->s4s_setup_charge = (float)get_field( 'quote_charges_s4s_setup_charge', 'option' );
    $config->setup_charge_threshold = (float)get_field( 'quote_charges_setup_charge_threshold', 'option' );

    $config->primed_matrix = gsm_get_priming_cost_matrix();
    $config->primed_cost = (float)get_field( 'quote_finishing_primed_cost', 'option' );
    $config->clearcoat_cost = (float)get_field( 'quote_finishing_clearcoat_cost', 'option' );
    $config->edge_glue_factor = (float)get_field( 'quote_charges_edge_glue_factor', 'option' );

    $config->labor_multiplier = (float)get_field( 'labor_multiplier', 'option' );
    $config->price_decimals = 2;

    // Added because Edge Glue Changed from decimal to percentage
    if ( $config->edge_glue_factor > 1.0 ) {
        $config->edge_glue_factor = $config->edge_glue_factor / 100.0;
    }

    $config->tiers = gsm_get_global_tiers();

    return $config;
}

function gsm_get_activate_quote_fields() {
    return array(
        'customer_name',
        'customer_name_shipment',
        'contact_name',
        'contact_name_shipment',
        'customer_phone',
        'customer_phone_shipment',
        'customer_city',
        'customer_city_shipment',
        'customer_email',
        'ship_address_same',
        'mill_notes',
        'customer_notes',
        'customer_po',
        'gslp_po',
        'lead_time',
        'tax_rate',
        'master_markup',
        'items',
        'gslp_location',
        'freight_cost',
        'is_order',
        'ltl_packaging',
        'order_status'
    );
}

function gsm_wp() {
    if ( ( is_front_page() || is_home() || is_page( 'orders' ) ) && !is_user_logged_in() ) {
        wp_redirect( wp_login_url( '/' ) );
        exit;
    }
}

function gsm_is_normal_sku( $sku ) {
    return ( $sku != 'custom' && $sku != 's4s' );
}

function gsm_get_active_pricing_defaults() {
    $active_pricing = new stdClass;

    $active_pricing->date = time();
    $active_pricing->species = gsm_get_all_species();

    return $active_pricing;
}

function gsm_load_quote_data( $quote_id = false ) {
    $quote_data = new stdClass;

    if ( !$quote_id && isset( $_GET[ 'gsm_quote_id' ] ) ) {
        $quote_id = $_GET[ 'gsm_quote_id' ];
    }

    // TODO: check post to make sure we have rights to edit it
    $quote_data->active_pricing = false;
    if ( $quote_id ) {
        $quote_data->active_pricing = get_post_meta( $quote_id, 'active_pricing', true );
    }

    if ( !$quote_data->active_pricing ) {
        $quote_data->active_pricing = gsm_get_active_pricing_defaults();

        if ( $quote_id ) {
            update_post_meta( $quote_id, 'active_pricing', $quote_data->active_pricing );
        }
    }

    // Let save the active config for the quotation
    $quote_data->active_config = false;
    if ( $quote_id ) {
        $quote_data->active_config = get_post_meta( $quote_id, 'active_config', true );

        if ( $quote_data->active_config ) {
            // Let's check tiers

            $updated = false;
            if ( !isset( $quote_data->active_config->tiers ) ) {
                $quote_data->active_config->tiers = gsm_get_global_tiers();

                $updated = true;
            }
            
            if ( !isset( $quote_data->active_config->primed_matrix ) ) {
                $quote_data->active_config->primed_matrix = gsm_get_priming_cost_matrix();

                $updated = true;
            }

            if ( $updated ) {
                update_post_meta( $quote_id, 'active_config', $quote_data->active_config );
            }
        }
    }

    if ( !$quote_data->active_config ) {
        $quote_data->active_config = gsm_get_configuration();

        update_post_meta( $quote_id, 'active_config', $quote_data->active_config );
    }

    if ( !$quote_id ) {
        $user = wp_get_current_user(); 
        $quote_data->author_name = $user->display_name;
        $quote_data->author_id = $user->ID;
    } else {
        $author_id = get_post_field( 'post_author', $quote_id );
        $quote_data->author_name = get_the_author_meta( 'display_name', $author_id );
        $quote_data->author_id = $author_id;
    }
    
    $quote_fields = gsm_get_activate_quote_fields();
    foreach( $quote_fields as $field ) {
        // Load field
        $data = false;
        if ( $quote_id ) {
            $data = get_post_meta( $quote_id, $field, true );
        } 
        
        if ( !$data ) {
            switch( $field ) {
                case 'order_status':
                    $quote_data->order_status = gsm_get_default_order_status();
                    break;
                case 'gslp_location':
                    $author_id = get_current_user_id();
                    if ( $quote_id ) {
                        $author_id = get_post_field( 'post_author', $quote_id );
                    } 
   
                    $quote_data->gslp_location = get_field( 'custom_user_location', 'user_' . $author_id );
                    if ( $quote_data->gslp_location == 'GSM' ) {
                        $quote_data->gslp_location = 'NJ';
                    }
                    break;
                case 'ltl_packaging':
                    $quote_data->ltl_packaging = 0;
                    break;
                case 'is_order':
                    $quote_data->is_order = 0; // it's not an order, it's a quote
                    break;
                case 'lead_time':
                    $quote_data->lead_time = gsm_get_one_configuration( 'default_lead_time' );
                    break;
                case 'tax_rate':
                    if ( $data === false ) {
                        $quote_data->tax_rate = gsm_get_one_configuration( 'tax_rate' );
                    } else {
                        $quote_data->tax_rate = 0;
                    }
                    
                    break;
                case 'master_markup':
                    $quote_data->master_markup = 'auto';
                    break;
                case 'freight_cost':
                    $quote_data->freight_cost = 0;
                    break;
                case 'ship_address_same':
                    $quote_data->ship_address_same = false;
                    break;
                default:
                    $quote_data->{$field} = null;
                    break;
            }
        } else {
            $quote_data->{$field} = $data;
        }
    } 

    if ( isset( $quote_data->items ) && is_array( $quote_data->items ) && count( $quote_data->items ) ) {
        $new_items = array();

        foreach( $quote_data->items as $one_item ) {
            $this_new_item = $one_item;

            $this_new_item[ 'setup_charge_type' ] = 'normal';

            $moulding_info = gsm_get_one_moulding_info( $one_item[ 'post_id' ] );
            if ( !$moulding_info ) {
                GSM_DEBUG( "ERROR CANT FIND MOULDING " . $one_item[ 'sku' ] . ' => ' . $one_item[ 'post_id' ] );

                $try_to_find_post_id = gsm_get_one_moulding_by_sku( $one_item[ 'sku' ] );
                if ( is_numeric( $try_to_find_post_id ) ) {
                    $moulding_info = gsm_get_one_moulding_info( $try_to_find_post_id );

                    // Fixing post id
                    $one_item[ 'post_id' ] = $try_to_find_post_id;

                    GSM_DEBUG( 'ERROR - FOUND ENTRY, UPDATING . ' . $try_to_find_post_id );
                }
            }

            if ( $moulding_info && gsm_is_normal_sku( $moulding_info->name ) ) {
                $moulding_info->pricing = gsm_get_all_ripsku_pricing( $moulding_info->ripsku, $quote_data->active_pricing->species );

                $moulding_info->mill_drawing = false;
                $possible_drawing = get_field( 'mill_drawing', $one_item[ 'post_id' ] );
                if ( $possible_drawing ) {
                    $moulding_info->mill_drawing = $possible_drawing;
                }

                if ( get_field( 'setup_charge', $one_item[ 'post_id' ] ) == 's4s' ) {
                    $this_new_item[ 'setup_charge_type' ] = 's4s';
                }  
            }
            
            $this_new_item[ 'moulding_info' ] = $moulding_info;

            $new_items[] = $this_new_item;
        }

        $quote_data->items = $new_items;
    } 

    return $quote_data;
}

function gsm_cleanup_wood_slug( $wood ) {
    return strtolower( str_replace( ' ', '-', $wood ) );
}

function gsm_cleanup_text( $str ) {
    return ucwords( str_replace( '-', ' ', $str ) );
}

function gsm_get_price_for_line_item( $item, $quote_data, $for_the_mill = false ) {
    // Check for the setup charge override
    $include_setup_charge = true;
    if ( isset( $item[ 'include_setup_charge' ] ) && $item[ 'include_setup_charge' ] == 0 ) {
        $include_setup_charge = false;
    }

    $species = $item[ 'species' ];

    // Check for item sanity
    $valid_item = true;
    if ( !isset( $item[ 'sku' ] ) ) {
        $valid_item = false;
    }

    if ( isset( $item[ 'sku' ] ) && ( $item[ 'sku' ] != 'custom' && $item[ 'sku' ] != 's4s' ) && !isset( $item[ 'moulding_info' ]->width ) ) {
        $valid_item = false;
    }
    
    if ( !$valid_item ) {
        GSM_DEBUG( 'PROBLEM WITH ITEM ' . $quote_data->customer_name . ' ' . print_r( $item, true ) );
      //  die;
    }

   // file_put_contents( dirname( __FILE__ ) . '/debug/debug' . '-' . $item[ 'sku' ] . '.txt', print_r( $quote_data, true ) . print_r( $item, true ) . print_r( $item[ 'moulding_info' ], true ) );

    $sku = $item[ 'sku' ];

    $price = new stdClass;
    $price->base_cost = 0;
    $price->knife_cost = 0;
    $price->setup_cost = 0;
    $price->edge_glue_cost = 0;
    $price->finish_cost = 0;
    $price->quantity = $item[ 'quantity' ];

    $internal_quantity = $item[ 'quantity' ];
    if ( $item[ 'unit_of_measure' ] == 'pc' ) {
        $internal_quantity = $internal_quantity * $item[ 'unit_pc_length' ];
    }

    $price->internal_quantity = $internal_quantity;

    //GSM_DEBUG( print_r( $item, true ) );
     
    // When the MILL PDF is generated, all FIXED PRICE overrides MUST use DEFAULT pricing
    $this_behaviour = $item[ 'custom_behavior' ];
    if ( $this_behaviour == 'override-final' ) {
        $this_behaviour = 'default';
    }

    $price->custom_behaviour = $item[ 'custom_behavior' ];

   // GSM_DEBUG( "Area 2" . print_r( $price, true ) );
    if ( $item[ 'species' ] == 'custom' ) {
        // Only for custom species, basically override all pricing
        if ( !$for_the_mill ) {
            $price->price = $item[ 'override_charge' ] * $item[ 'quantity' ];
             if ( isset( $item[ 'override_gsm'] ) && $item[ 'override_gsm' ] >= 0 ) {
                $price->price_for_global_markup = $item[ 'override_gsm' ] * $item[ 'quantity' ];
            }
        } else { 
            if ( isset( $item[ 'override_gsm'] ) && $item[ 'override_gsm' ] >= 0 ) {
                $price->price = $item[ 'override_gsm' ] * $item[ 'quantity' ];
                $price->price_for_global_markup = $item[ 'override_gsm' ] * $item[ 'quantity' ];
            } else {
                $price->price = 0;
                $price->price_for_global_markup = 0;
            }
        }
    } else if ( $sku == 'custom' || $sku == 's4s' ) {
        $all_species = gsm_get_all_species_base( $quote_data->active_pricing->species );
        $ripsku = $item[ 'custom_ripsku' ];
        $parts = explode( 'RIP', $ripsku  );
        $factor = gsm_get_thickness_factor( $parts[ 0 ] ) * $parts[ 1 ] / 12.0;
        $actual_wood_cost = $all_species[ $item[ 'species' ] ]->marked_up_yielded_costs[ $parts[ 0 ] ];
        $actual_wood_cost_with_factor = sprintf( '%0.5f', $actual_wood_cost * $factor / 1000.0 );

        $item_width = $parts[ 1 ];

        GSM_DEBUG( 'Custom width should be ' . $item_width );

        // Custom
        switch( $this_behaviour ) {
            case 'default':
                $cost_quantity_amount = $actual_wood_cost_with_factor * $internal_quantity;

                $price->base_cost = $actual_wood_cost_with_factor;

                $setup_cost = 0;
                if ( $cost_quantity_amount < $quote_data->active_config->setup_charge_threshold ) {    
                    if ( $sku == 's4s' ) {
                        $setup_cost = $quote_data->active_config->s4s_setup_charge;
                    } else if ( $sku == 'custom' ) {
                        $setup_cost = $quote_data->active_config->custom_setup_charge;
                    }
                }

                if ( !$include_setup_charge ) {
                    $setup_cost = 0;
                }

                $price->setup_cost = $setup_cost;
                $price->edge_glue_factor = $quote_data->active_config->edge_glue_factor;

                $edge_glue_cost = 0;
                if ( $item[ 'edge_glue' ] ) {
                    $edge_glue_cost = $actual_wood_cost_with_factor * $quote_data->active_config->edge_glue_factor * $internal_quantity;
                }

                $price->edge_glue_cost = $edge_glue_cost;

                $finish_cost = 0;
                if ( $item[ 'finish' ] == 'primed' ) {
                    if ( isset( $quote_data->active_config->primed_matrix ) ) {
                        $priming_cost_per_um = gsm_get_priming_cost( $item[ 'custom_width' ], $quote_data->active_config->primed_matrix );
                        $finish_cost = $internal_quantity * $priming_cost_per_um;

                        GSM_DEBUG( 'CUSTOM: Using matrix priming cost of ' . $priming_cost_per_um . ' for width ' . $item_width );
                    } else {
                        $finish_cost = $quote_data->active_config->primed_cost * $internal_quantity;
                    }     
                } else if ( $item[ 'finish' ] == 'clear-coat' ) {
                    $finish_cost = $quote_data->active_config->clearcoat_cost * $internal_quantity;
                }

                $price->finish_cost = $finish_cost;

                $knife_cost = 0;
                if ( $item[ 'knife_charge' ] ) {
                    $knife_cost = ( ceil( $item[ 'custom_width' ] ) * $quote_data->active_config->knife_charge );
                }

                $price->knife_cost = $knife_cost;

                $price->price = ( $setup_cost + $knife_cost ) + ( $finish_cost  + $edge_glue_cost ) + $actual_wood_cost_with_factor * $internal_quantity;

                break;
            case 'override-individual':
                $price->base_cost = $actual_wood_cost_with_factor;
                $price->setup_cost = $item[ 'override_setup' ];
                $price->edge_glue_cost = isset( $item[ 'override_edge' ] ) ? floatval( $item[ 'override_edge' ] ) : floatval( 0.0 );
                $price->finish_cost = $item[ 'override_finish' ];
                $price->knife_cost = $item[ 'override_knife' ];

                if ( !$include_setup_charge ) {
                    $price->setup_cost = 0;
                }

                $edge_cost = isset( $item[ 'override_edge' ] ) ? floatval( $item[ 'override_edge' ] ) : floatval( 0.0 );
                $price->price = ( $price->setup_cost + $item[ 'override_knife' ] ) + ( $actual_wood_cost_with_factor + $edge_cost + $item[ 'override_finish' ] ) * $internal_quantity;

                // Need to scale these up
                $price->finish_cost = $price->finish_cost * $internal_quantity;
                $price->edge_glue_cost = $price->edge_glue_cost * $internal_quantity;

                break;
            case 'override-final':
                $price->price = $item[ 'override_charge' ] * $item[ 'quantity' ];
                break;
        }

        if ( defined( 'GSM_DEBUG_ON' ) && GSM_DEBUG_ON == 1 ) { 
            $f = fopen( dirname( __FILE__ ) . '/debug' . '-' . $item[ 'sku' ] . '-all.txt' , 'a+t' );
            fprintf( $f, print_r( $price, true ) . "\n" . print_r( $quote_data->active_config->primed_matrix, true ) . " " . $item_width . "\n" );
            fclose( $f );
        }

        if ( $item[ 'custom_behavior' ] != 'override-final' ) {  
            // check for GSM adjust charge, only defined for default and override-individual
            if ( isset( $item[ 'gsm_adjust' ] ) && ( $item[ 'gsm_adjust' ] > 1.0 || $item[ 'gsm_adjust'] < 1.0 ) ) {
                $price->price = $price->price * $item[ 'gsm_adjust' ];
            }
        }
       
        $price->price_for_global_markup = $price->price;
        if ( $item[ 'custom_behavior' ] == 'override-final' ) {  
            if ( !$for_the_mill ) {
                $price->price = $item[ 'override_charge' ] * $item[ 'quantity' ];
            } else {
                if ( isset( $item[ 'override_gsm' ] ) && $item[ 'override_gsm' ] >= 0 ) {
                    $price->price = $item[ 'override_gsm' ] * $item[ 'quantity' ];
                }
            }
        }
    } else {
        // Normal SKUs
        $base_price = $internal_quantity * $item[ 'moulding_info' ]->pricing[ $species ];
        $price->base_cost_per_um = $item[ 'moulding_info' ]->pricing[ $species ];
        $price->base_cost = $base_price;

        if ( !isset( $item[ 'moulding_info' ]->width ) ) {
          //  GSM_DEBUG( 'WIDTH NOT SET' . print_r( $quote_data, true ) . "\n\n" . print_r( $item, true ) );
            //die;
        }

        $item_width = $item[ 'moulding_info' ]->width;

        GSM_DEBUG( 'NORMAL SKU - Normal width should be ' . $item_width );

        switch( $this_behaviour ) {
            case 'default':
                $price->price = $base_price;

                if ( $price->price < $quote_data->active_config->setup_charge_threshold ) {
                    $my_post_id = gsm_get_id_from_sku( $item[ 'sku'] );
                    $price->setup_cost = $quote_data->active_config->custom_setup_charge; 

                    if ( $my_post_id ) {
                        $sku_type = get_field( 'setup_charge', $my_post_id );
                        if ( $sku_type == 's4s' ) {
                            $price->setup_cost = $quote_data->active_config->s4s_setup_charge; 
                        }
                    }
                }

                if ( !$include_setup_charge ) {
                    $price->setup_cost = 0;
                }

                $price->price = $price->price + $price->setup_cost;
                
                //GSM_DEBUG( "Area 3" . print_r( $price, true ) );
                
                if ( $item[ 'finish' ] == 'primed' ) {
                    if ( isset( $quote_data->active_config->primed_matrix ) ) {
                        $priming_cost_per_um = gsm_get_priming_cost( $item_width, $quote_data->active_config->primed_matrix );
                        $price->priming_cost_per_um =  $priming_cost_per_um;
                        $price->finish_cost = $internal_quantity * $priming_cost_per_um;

                        GSM_DEBUG( 'NORMAL: Using matrix priming cost of ' . $priming_cost_per_um . ' for width ' . $item_width );
                    } else {
                        $price->finish_cost = $internal_quantity * $quote_data->active_config->primed_cost;
                    }
                    
                } else if ( $item[ 'finish' ] == 'clear-coat' ) {
                    $price->finish_cost = $internal_quantity * $quote_data->active_config->clearcoat_cost;
                } 

                //GSM_DEBUG( "Area 4" . print_r( $price, true ) );

                $price->price = $price->price + $price->finish_cost;
                $price->edge_glue_factor = $quote_data->active_config->edge_glue_factor;

                if ( $item[ 'edge_glue' ] ) {
                    $price->edge_glue_cost = $base_price * $quote_data->active_config->edge_glue_factor;
                }

                $price->price = $price->price + $price->edge_glue_cost;

                break;
            case 'override-individual':
                $price->setup_cost = $item[ 'override_setup' ];
                $price->edge_glue_cost = isset( $item[ 'override_edge' ] ) ? floatval( $item[ 'override_edge' ] ) : floatval( 0.0 );
                $price->finish_cost = $item[ 'override_finish' ];
                $price->knife_cost = $item[ 'override_knife' ];

                if ( !$include_setup_charge ) {
                    $price->setup_cost = 0;
                }

                $edge_cost = isset( $item[ 'override_edge' ] ) ? floatval( $item[ 'override_edge' ] ) : floatval( 0.0 );
                $price->price = ( floatval( $price->setup_cost ) + floatval( $item[ 'override_knife' ] ) ) + $base_price + ( floatval( $edge_cost ) + floatval( $item[ 'override_finish' ] ) ) * $internal_quantity;
                
                // need to scale this up
                $price->finish_cost = $price->finish_cost * $internal_quantity;
                $price->edge_glue_cost = $price->edge_glue_cost * $internal_quantity;
                break;
        }

        if ( $item[ 'custom_behavior' ] != 'override-final' ) {  
            // check for GSM adjust charge, only defined for default and override-individual
            if ( isset( $item[ 'gsm_adjust' ] ) && ( $item[ 'gsm_adjust' ] > 1.0 || $item[ 'gsm_adjust' ] < 1.0 ) ) {
                $price->price = $price->price * $item[ 'gsm_adjust' ];
            }
        }

        $price->price_for_global_markup = $price->price;
        if ( $item[ 'custom_behavior' ] == 'override-final' ) {  
            if ( !$for_the_mill ) {
                $price->price = $item[ 'override_charge' ] * $item[ 'quantity' ];
            } else {
                if ( isset( $item[ 'override_gsm'] ) && $item[ 'override_gsm' ] >= 0 ) {
                    $price->price = $item[ 'override_gsm' ] * $item[ 'quantity' ];
                }
            }
        }
    }

    return $price;
}

function gsm_calculate_quote_pricing( $quote_data, $quote_id, $for_the_mill = false ) {
    $pricing_data = new stdClass;

    $pricing_data->freight_cost = $quote_data->freight_cost;
    $pricing_data->total_tax = 0;
    $pricing_data->price = 0;
    $pricing_data->raw_price = 0;
    $pricing_data->included_markup = 0;
    $pricing_data->line_items = array();

    // Calculate each line item price without markup
    if ( $quote_data->items ) {
        foreach( $quote_data->items as $item ) {
            if ( isset( $item[ 'is_active' ] ) && $item[ 'is_active' ] == 0 ) {
                continue;
            }

            $processed_item = new stdClass;
            $processed_item->data = $item;
    
            $processed_item->price_data = gsm_get_price_for_line_item( $item, $quote_data, $for_the_mill );

           // print_r( $processed_item->price_data );
           // die;

          //  GSM_DEBUG( "PRICING FOR LINE ITEM IS -> " . $processed_item->price_data->price );
            if ( $for_the_mill ) {
                $price_per_each = sprintf( '%0.2f', $processed_item->price_data->price / $processed_item->price_data->quantity );
                $processed_item->price_data->price = $price_per_each * $processed_item->price_data->quantity;
            }
    
            $processed_item->price = $processed_item->price_data->price;

            $pricing_data->price += $processed_item->price;

        //    if ( $item->data[ 'custom_behavior' ] != 'override-final' ) {
                if ( isset( $processed_item->price_data->price_for_global_markup ) ) {
                    $pricing_data->raw_price += $processed_item->price_data->price_for_global_markup;
                }
                
        //    }
    
            $pricing_data->line_items[] = $processed_item;
        }
    }

    GSM_DEBUG( "TOTAL RAW PRICE FOR ALL IS -> " . $pricing_data->raw_price );
    GSM_DEBUG( "TOTAL PRICE FOR ALL IS -> " . $pricing_data->price );

    //echo $pricing_data->price;

   // print_r( $quote_data->active_config->tiers[ 'more_20000' ] ); die; ///->tiers[ 'more_20000' ] ); die;
    // Figure out markup to apply
    $pricing_data->global_markup = $quote_data->master_markup;
    if ( $pricing_data->global_markup == 'auto' ) {
        if ( $pricing_data->raw_price > 20000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_20000' ];
        } else if ( $pricing_data->raw_price > 15000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_15000' ];
        } else if ( $pricing_data->raw_price > 10000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_10000' ];
        } else if ( $pricing_data->raw_price > 7500 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_7500' ];
        } else if ( $pricing_data->raw_price > 5000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_5000' ];
        } else if ( $pricing_data->raw_price > 3000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_3000' ];
        } else if ( $pricing_data->raw_price > 1000 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_1000' ];
        } else if ( $pricing_data->raw_price > 500 ) {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'more_500' ];
        } else {
            $pricing_data->global_markup = $quote_data->active_config->tiers[ 'less_equal_500' ];
        }
    }

    // Backfill the markup to each line item
    foreach( $pricing_data->line_items as $item ) {
        if ( isset( $item->data[ 'is_active' ] ) && $item->data[ 'is_active' ] == 0 ) {
            continue;
        }

        $item->applied_markup = 0.0;
        $item->included_markup = 0;

        if ( $item->data[ 'custom_behavior' ] == 'override-final' ) {
            // When there is an override of the total price, we don't add markup on top of it
            // let's filter the price here

          //  $pricing_data->price = $pricing_data->price - $item->price;
                    // let's filter the price here
            continue;
        }

        $markup_to_use = $pricing_data->global_markup;
        if ( $item->data[ 'markup' ] != 'default' ) {
            $markup_to_use = $item->data[ 'markup' ];
        }

        $item->applied_markup = $markup_to_use;

        // Markup
        $item->included_markup = 0;
        if ( !$for_the_mill ) {
            $item->included_markup = ( $item->applied_markup - 1.0 ) * $item->price;
        }

        $old_price = $item->price;
        $item->price += $item->included_markup;

        // let's filter the price here
        $price_per_each = sprintf( '%0.2f', $item->price / $item->data[ 'quantity' ] );
        $item->price = $price_per_each * $item->data[ 'quantity' ];

        $price_diff = $item->price - $old_price;
        $item->included_markup = $price_diff;

        $pricing_data->included_markup += $item->included_markup;
        $pricing_data->price += $item->included_markup;
    }

    // Now we can update the price/lf
    foreach( $pricing_data->line_items as $item ) {
        if ( isset( $item->data[ 'is_active' ] ) && $item->data[ 'is_active' ] == 0 ) {
            continue;
        }

        $item->price_per_lf = 0;
        if ( $item->data[ 'quantity' ] ) {
            $item->price_per_lf = sprintf( "%0.2f", $item->price / (float)$item->data[ 'quantity' ] );
        }
    }

    // Limit decimals
    $pricing_data->price = sprintf( '%0.2f', $pricing_data->price );

    if ( $pricing_data->freight_cost && !$for_the_mill ) {
        $pricing_data->price += $pricing_data->freight_cost;
    } else {
        $pricing_data->freight_cost = 0;
    }

    $pricing_data->ltl_packaging = 0;
    if ( $quote_data->ltl_packaging == '1' && $for_the_mill ) {
        $pricing_data->ltl_packaging = 125;

        $pricing_data->price = $pricing_data->price + $pricing_data->ltl_packaging;
    }

    // Apply tax
    $pricing_data->applied_tax = 0;
    $pricing_data->price_before_tax = $pricing_data->price;
    $pricing_data->applied_tax_rate = 0;
    if ( $quote_data->tax_rate > 0 && !$for_the_mill ) {
        $pricing_data->applied_tax_rate = $quote_data->tax_rate;

        $pricing_data->included_tax = $pricing_data->price_before_tax * ( $quote_data->tax_rate / 100.0 );
        $pricing_data->price = $pricing_data->price + $pricing_data->included_tax;
    }

    return $pricing_data;
}

function gsm_maybe_add_price_to_string( $list, $price, $template, $str = false ) {
    if ( $price > 0.00 ) {
        $list[] = sprintf( $template, $price, $str );
    }

    return $list;
}

function gsm_lf_to_br( $str ) {
    return str_replace( "\n", '<br/>', $str );
}

function gsm_get_lead_time( $quote_data ) {
    $lead_time = $quote_data->lead_time;

    if ( $quote_data->lead_time == 'auto' ) {
        // we need to calculate it
        $lead_time = 3;
        foreach( $quote_data->items as $item ) {
            GSM_DEBUG( print_r( $item, true ) );
            if ( $item[ 'sku' ] == 'custom' || $item[ 'finish' ] == 'primed' || $item[ 'finish' ] == 'clear-coat' ) {
                $lead_time = 4;
                break;
            }
        }
    }

    $friendly_lead_time = sprintf( _n( '%s week', '%s weeks', $lead_time ), $lead_time );

    return $friendly_lead_time;
}

function gsm_get_email_body_content( $quote_data, $quote_id, $private_info = false, $for_the_mill = false, $author_id = 0 ) {
    $tax_template = file_get_contents( dirname( __FILE__ ) . '/html/shared/quote-tax.html' );
    $freight_template = file_get_contents( dirname( __FILE__ ) . '/html/shared/quote-freight.html' );
    $ltl_template = file_get_contents( dirname( __FILE__ ) . '/html/shared/quote-ltl.html' );

    $shipment_template = '';
    if ( $for_the_mill ) {
        $html_template = file_get_contents( dirname( __FILE__ ) . '/html/mill/quote-body.html' );
        $table_content_template = file_get_contents( dirname( __FILE__ ) . '/html/mill/quote-line-item.html' );
    } else {
        $html_template = file_get_contents( dirname( __FILE__ ) . '/html/customer/quote-body.html' );
        $table_content_template = file_get_contents( dirname( __FILE__ ) . '/html/customer/quote-line-item.html' );
        if ( $quote_data->ship_address_same ) {
            $shipment_template = file_get_contents( dirname( __FILE__ ) . '/html/customer/shipment-address.html' );

            $shipment_template = str_replace( 
                array(
                    '{{customernameship}}',
                    '{{contactnameship}}',
                    '{{customercityship}}',
                    '{{customerphoneship}}'
                ),
                array(
                    $quote_data->customer_name_shipment,
                    $quote_data->contact_name_shipment,
                    $quote_data->customer_city_shipment,
                    $quote_data->customer_phone_shipment
                ),
                $shipment_template
            );
        }
    } 

    $pricing_data = gsm_calculate_quote_pricing( $quote_data, $quote_id, $for_the_mill );

  //  GSM_DEBUG( 'After Email Quote Pricing' . print_r( $pricing_data, true ) );

    $table_content = '';

    if ( $author_id == 0 ) {
        $author_id = get_post_field( 'post_author', $quote_id );
    }
    
    $display_name = get_the_author_meta( 'display_name' , $author_id ); 
    $author_email = get_the_author_meta( 'user_email' , $author_id );
    
    foreach( $pricing_data->line_items as $item ) {
        if ( isset( $item->data[ 'is_active' ] ) ) {
            if ( $item->data[ 'is_active' ] == 0 ) {
                continue;
            }
        }

        $note_text = '';
        $profile_image = '';

        if ( $item->data[ 'notes' ] ) {
            $note_text = gsm_lf_to_br( '<em>Note: ' . $item->data[ 'notes' ] . '</em>' );
        }

        $profile = $item->data[ 'moulding_info' ]->profile;

        if ( $item->data[ 'sku' ] == 'custom' || $item->data[ 'sku' ] == 's4s' ) {
            $dims = $item->data[ 'custom_thickness_friendly' ] . '" x ' . $item->data[ 'custom_width_friendly' ] . '"';

            
            $profile_image = gsm_get_image_for_sku( $item->data[ 'sku' ] );
            if ( $profile_image ) {
                $profile_image = '<img src="' . esc_url( $profile_image ) . '" style="max-width: 80; max-height: 80" />';
            }

            if ( $item->data[ 'custom_desc' ] && strlen( $item->data[ 'custom_desc' ] ) ) {
                $profile = $item->data[ 'custom_desc' ];
            }
        } else {
            $dims = $item->data[ 'moulding_info' ]->friendly_thickness . '" x ' . $item->data[ 'moulding_info' ]->friendly_width . '"';

            $profile_image = gsm_get_image_for_sku( $item->data[ 'sku' ] );
            if ( $profile_image ) {
                $profile_image = '<img src="' . esc_url( $profile_image ) . '" style="max-width: 80; max-height: 80" />';
            }
        }

        GSM_DEBUG( 'PROFILE => ' . $profile );

        $finish_params = array();

        $finish = $item->data[ 'finish' ];
        if ( $finish == 'unfinished' ) {
            $finish = '';
        }

        if ( $finish ) {
            $finish_params[] = $finish;
        }

        if ( $item->data[ 'edge_glue' ] == 1 ) {
            $finish_params[] = 'EDGE GLUED';
        }

        $finish = implode( ' · ', $finish_params );

        $sku = strtoupper( $item->data[ 'moulding_info' ]->name );
        if ( $item->data[ 'custom_sku' ] ) {
            $sku = $item->data[ 'custom_sku' ];
        }

        $mill_drawing = false;
        if ( $item->data[ 'post_id' ] ) {  
            if ( is_numeric( $item->data[ 'post_id' ] ) ) {
                $mill_drawing = get_field( 'mill_drawing', $item->data[ 'post_id' ] );

                if ( $mill_drawing ) {
                    $mill_drawing = $mill_drawing[ 'url' ];
                }
            }
            
            if ( !$mill_drawing ) {
                if ( isset( $item->data[ 'drawing_url' ] ) ) {
                    $mill_drawing = $item->data[ 'drawing_url' ];
                }
            }
        }

        if ( $mill_drawing ) {
            $sku = $sku . ' <a href="' . esc_url( $mill_drawing ) . '" target="_blank">[DRAWING]</a>';
        }

        $this_species = $item->data[ 'species' ];
        if ( $this_species == 'custom' ) {
            $this_species = $item->data[ 'species_custom' ]; 
        }

        $price_front = '';
        $price_back = '';

        $includes = '';
        if ( $for_the_mill ) {
            $price_list = array();

            $price_list = gsm_maybe_add_price_to_string( $price_list, $item->price_data->setup_cost, 'SETUP: $%0.2f' );
            $price_list = gsm_maybe_add_price_to_string( $price_list, $item->price_data->knife_cost, 'KNIFE: $%0.2f' );
            $price_list = gsm_maybe_add_price_to_string( $price_list, $item->price_data->finish_cost/$item->data[ 'quantity' ], 'FINISH: $%0.2f/%s', $item->data[ 'unit_of_measure' ] );
            $price_list = gsm_maybe_add_price_to_string( $price_list, $item->price_data->edge_glue_cost/$item->data[ 'quantity' ], 'EDGE: $%0.2f/%s', $item->data[ 'unit_of_measure' ] );

            if ( count( $price_list ) ) {
                $includes = "<small>" . implode( '<br/>', $price_list ) . '</small>';
            }

            // Add red to the quote
            if ( isset( $item->data[ 'override_gsm' ] ) && $item->data[ 'override_gsm' ] >= 0 ) {
                $price_front = '<span style="font-weight: bold; color: #dc3545;">';
                $price_back = '</span>';
            }
        }
  
        if ( $item->data[ 'unit_of_measure' ] == 'pc' ) {
            $finish = $finish . '<br/><br/>' . $item->data[ 'unit_pc_length' ] . '\' LENGTHS';
        }

        $html_line_item = str_replace(
            array(
                '{{quantity}}',
                '{{sku}}',
                '{{profile}}',
                '{{material}}',
                '{{finish}}',
                '{{note}}',
                '{{dimensions}}',
                '{{unitofmeasure}}',
                '{{priceperlf}}',
                '{{price}}',
                '{{profileimage}}',
                '{{includes}}'
            ),
            array(
                $item->data[ 'quantity' ],
                $sku,
                $profile,
                gsm_cleanup_text( $this_species ),
                $finish,
                $note_text,
                $dims,
                $item->data[ 'unit_of_measure' ],
                $price_front . '$' . number_format( $item->price_per_lf, 2 ) . $price_back, 
                $price_front . '$' . number_format( $item->price, 2 ) . $price_back,
                $profile_image,
                $includes
            ),
            $table_content_template
        );

        $table_content = $table_content . $html_line_item;
    }

    $customer_note = '';
    if ( $for_the_mill ) {
        if ( strlen( trim( $quote_data->mill_notes ) ) ) {
           // $customer_note = '<div style="color: white; line-spacing: 1.0px; padding: 5px; font-size: 12pt; text-align: center; background: #7C0D0E">Notes: ' . $quote_data->mill_notes . '</div>';
            $customer_note = '<span class="bold-note-title">Notes:</span><br/><span class="bold-note">' . $quote_data->mill_notes . '</span>';
        } else {
            $customer_note = '&nbsp;';
        }
    } else {
        if ( strlen( trim( $quote_data->customer_notes ) ) ) {
            $customer_note =  '<span class="bold-note-title">Notes:</span><br/><span class="bold-note">' . $quote_data->customer_notes . '</span>';
        } else {
            $customer_note = '&nbsp;';
        }
    }

    if ( $pricing_data->applied_tax_rate != 0 ) {
        $tax_template = str_replace(
            array(
                '{{totaltax}}',
                '{{taxrate}}',
                '{{subtotalprice}}'
            ),
            array(
                '$' . number_format( $pricing_data->included_tax, 2 ),
                number_format( $pricing_data->applied_tax_rate, 2 ),
                '$' . number_format( $pricing_data->price_before_tax, 2 )
            ),
            $tax_template
        );
    } else {
        $tax_template = '';
    }

    $ltl_notice = '';
    if ( $quote_data->ltl_packaging == 1 && $for_the_mill ) {
        $ltl_template = str_replace( 
            array( '{{subtotalprice}}', '{{totalltl}}' ), 
            array( 
                '$' . number_format( $pricing_data->price - $pricing_data->ltl_packaging, 2 ), 
                '$' . number_format( $pricing_data->ltl_packaging, 2 )
            ), 
            $ltl_template 
        );

        $ltl_notice = '<br /><div style="display: block; font-size: 18pt; font-weight: bold; background: red; color: white; padding: 50px">LTL PACKAGING</div>';
    } else {
        $ltl_template = '';
    }

    if ( $pricing_data->freight_cost ) {
        $freight_template = str_replace(
            array( '{{freight}}' ),
            array( '$' . number_format( $pricing_data->freight_cost, 2 ) ),
            $freight_template
        );
    } else {
        $freight_template = '';
    }

    $gsl_address = '';
    $locations = gsm_get_all_locations();
    if ( $quote_data->gslp_location ) {
        if ( isset( $locations[ $quote_data->gslp_location ] ) ) {
            $gsl_address = str_replace( "\n", "<br />", $locations[ $quote_data->gslp_location ]->address );
        }
    }

    $friendly_location = $quote_data->gslp_location;
    if ( isset( $locations[ $quote_data->gslp_location ] ) ) {
        $friendly_location = $locations[ $quote_data->gslp_location ]->name;
    }
    
    $all_notes = 'All prices shown include knife and setup charges unless otherwise indicated';

    if ( !$for_the_mill ) {
        $all_notes = $all_notes . "<br/><br /><span style='color: darkred'>PLEASE NOTE: The average mill overrun will range between 5%-10% on all items ordered</span>";
    }

    $actual_is_order = get_post_meta( $quote_id, 'is_order', true );
    $quote_valid = '&nbsp;';
    if ( !$for_the_mill && $actual_is_order == 0 ) {
        $quote_valid = 'Quotes are valid for 30 days unless otherwise indicated';
    }

    $friendly_lead_time = gsm_get_lead_time( $quote_data );

    $html_template = str_replace( 
        array(
            '{{customername}}',
            '{{contactname}}',
            '{{customercity}}',
            '{{customeremail}}',
            '{{customerphone}}',
            '{{date}}',
            '{{quoteid}}',
            '{{salesperson}}',
            '{{location}}',
            '{{estimatedarrival}}',
            '{{gslppo}}',
            '{{customerpo}}',
            '{{tablebody}}',
            '{{tax_template}}',
            '{{freight_template}}',
            '{{totalprice}}',
            '{{imageurl}}',
            '{{customernotes}}',
            '{{gsl_address}}',
            '{{all_notes}}',
            '{{quote_valid}}',
            '{{ltl_template}}',
            '{{ltl_notice}}',
            '{{shipmentaddress}}'
        ),
        array(
            $quote_data->customer_name,
            $quote_data->contact_name,
            $quote_data->customer_city,
            $quote_data->customer_email,
            $quote_data->customer_phone,
           // date( 'F jS, Y', $quote_data->active_pricing->date ),
            get_the_date( 'F jS, Y', $quote_id ),
            '#' . $quote_id,
            $display_name,
            $friendly_location,
            $friendly_lead_time,
            $quote_data->gslp_po,
            $quote_data->customer_po,
            $table_content,
            $tax_template,
            $freight_template,
            '$' . number_format( $pricing_data->price, 2 ),
            get_stylesheet_directory_uri() . '/images',
            $customer_note,
            $gsl_address,
            $all_notes,
            $quote_valid,
            $ltl_template,
            $ltl_notice,
            $shipment_template
        ),
        $html_template
    );

    return $html_template;
}

function gsm_get_thickness_factor( $thick ) {
    $factor_array = array(
        '44' => 1.0,
        '54' => 1.25,
        '64' => 1.5,
        '74' => 1.75,
        '84' => 2,
        '104' => 2.5,
        '124' => 3.0
    );

    return $factor_array[ $thick ];
}

function gsm_get_ripsku_approx_array() {
    return array(
        '44' => 0.75,
        '54' => 1.0625,
        '64' => 1.25,
        '74' => 1.5,
        '84' => 1.75,
        '104' => 2.25,
        '124' => 2.75  
    );
}

function gsm_get_ripsku_data( $thickness, $width ) {
    $response = new stdClass;

    $response->thickness = $thickness;
    $response->width = $width;

    $response->ripsku_thickness = false;
    $response->ripsku_width = false;

    // NEED TO UPDATE BASED ON ALLAN
    $thickness_array = gsm_get_ripsku_approx_array();

    foreach( $thickness_array as $name => $this_thickness ) {
        if ( $thickness <= $this_thickness ) {
            $response->ripsku_thickness = $name;
        }

        if ( $response->ripsku_thickness ) {
            break;
        }
    }

    $nearest = ( $response->width + 0.25 ) / 0.25;
    $nearest_frac = $nearest - floor( $nearest );
    $nearest_base = floor( $nearest );
    $scaled = ( $nearest_base + 1 ) * 0.25;

    if ( $nearest_frac == 0 ) {
        $scaled = $nearest_base * 0.25;
    }

    $response->ripsku_width = $scaled;

    $response->nearest_base = $nearest_base;
    $response->nearest = $nearest;

    $response->rip_sku = $response->ripsku_thickness . 'RIP' . $response->ripsku_width;
    $response->rip_sku_factor = gsm_get_thickness_factor( $response->ripsku_thickness ) * $response->ripsku_width / 12.0;

    GSM_DEBUG( print_r( $response, true ) );

    return $response;
}

function gsm_send_pdf_to_cust_or_mill( $quote_id, $destination_address, $response, $content, $author_id, $send_to_me = 0, $send_to_mill = false ) {
    $quote_data = gsm_load_quote_data( $quote_id );
    $response->info = $quote_data;

   // $author_id = get_post_field( 'post_author', $quote_id );
    $display_name = get_the_author_meta( 'display_name' , $author_id ); 
    $author_email = get_the_author_meta( 'user_email' , $author_id );

    $html_template = $content;

    $headers = array();
    $headers[] = 'Content-type: text/plain; charset=utf-8';
    $headers[] = 'From: ' . $display_name . ' <' . $author_email . '>';
    $subject = '';

    $all_addresses = explode( ',', $destination_address );

    $temp_dir = get_temp_dir();

    if ( $send_to_mill ) {
        $pdf_name = $temp_dir . '/Mill-' . gsm_get_quote_or_order( $quote_data ) . '-' . $quote_id . '-' . time() . '.pdf';
        $subject = 'Garden State Lumber, Mill ' . gsm_get_quote_or_order( $quote_data ) . ' #' . $quote_id;
    } else {
        $pdf_name = $temp_dir . '/' . gsm_get_quote_or_order( $quote_data ) . '-' . $quote_id . '-' . time() . '.pdf';
        $subject = 'Garden State Lumber, ' . gsm_get_quote_or_order( $quote_data ) . ' #' . $quote_id;
    }

    $mpdf = gsm_get_mpdf_for_quote( $quote_id, $send_to_mill, $author_id );
    $mpdf->Output( $pdf_name, 'F' );

    if ( $all_addresses ) {
        if ( $send_to_me ) {
            $all_addresses[] = $author_email;
        } 
        
        foreach( $all_addresses as $address ) {
            wp_mail(
                $address,
                $subject,
                $html_template,
                $headers,
                $pdf_name
            );
        }
    }

    return $response;
}

function gsm_get_active_pricing_for_quote_id( $quote_id ) {
    $active_pricing = false;

    if ( $quote_id ) {
        $active_pricing = get_post_meta( $quote_id, 'active_pricing', true );
    } 

    if ( !$active_pricing ) {
        $active_pricing = gsm_get_active_pricing_defaults();
    }

    return $active_pricing;
}

function gsm_duplicate_post( $post_id ) {
    $title   = get_the_title( $post_id );
    $oldpost = get_post( $post_id );

    $post    = array(
      'post_title' => $title,
      'post_status' => 'publish',
      'post_type' => $oldpost->post_type,
      'post_author' => $oldpost->post_author
    );

    $new_post_id = wp_insert_post( $post );
    // Copy post metadata

    $data = get_post_custom( $post_id );
    foreach ( $data as $key => $values ) {
        foreach ( $values as $value ) {
            add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
        }
    }

    return $new_post_id;
  }

function gsm_update_quote_to_current_time( $quote_id ) {
    $time = current_time( 'mysql' );

    wp_update_post(
        array (
            'ID'            => $quote_id, 
            'post_date'     => $time,
            'post_date_gmt' => get_gmt_from_date( $time )
        )
    );
}

function gsm_update_save_data( $post_id, $description ) {
    $quote_data = gsm_load_quote_data( $post_id );
    if ( $quote_data ) {
        $save_log = get_post_meta( $post_id, 'save_log', true );
        if ( !$save_log ) {
            $save_log = array();
        }

        $new_entry = new stdClass;
        $new_entry->user_id = get_current_user_id();
        $new_entry->user_name = get_author_name( $new_entry->user_id );
        $new_entry->log_time = microtime();
        $new_entry->friendly_time = wp_date( 'M jS, Y - g:i:s a' );
        $new_entry->desc = $description;

        $save_log[] = $new_entry;

        update_post_meta( $post_id, 'save_log', $save_log );
    }
}

function gsm_handle_ajax() {
    $action = $_POST[ 'gsm_action' ];
    $nonce = $_POST[ 'gsm_nonce' ];

    $response = new stdClass;
    $response->success = 0;

    if ( wp_verify_nonce( $nonce, 'ajax_nonce' ) ) {
        switch( $action ) {
            case 'save_overstock_items': 
                $all_files = array();

                $response->files = array(); 
                $response->data = $_FILES;
                if ( isset( $_FILES ) && isset( $_FILES[ 'images' ] ) ) {
                    $upload_dir = wp_upload_dir();
                    $location = $upload_dir[ 'basedir' ] . '/overstock/';
                    @mkdir( $location, 0775, true );

                    for ( $i = 0; $i < count( $_FILES[ 'images' ][ 'name' ] ); $i++ ) {
                        $tmp_name = $_FILES[ 'images' ][ 'tmp_name' ][$i];
                        $name = $_FILES[ 'images' ][ 'name' ][ $i ];

                        if ( strlen( $tmp_name ) && strlen( $name ) ) {
                            @unlink( $location . $name );
                            @rename( $tmp_name, $location . $name );
                            @chmod( $location . $name, 0644 );

                            $response->files[] = $upload_dir[ 'baseurl' ] .  '/overstock/' . $name;

                            $all_files[] = $upload_dir[ 'baseurl' ] .  '/overstock/' . $name;
                        }
                    }
                }

                if ( isset( $_POST[ 'post_id' ] ) ) {
                    $post_id = $_POST[ 'post_id' ];

                    if ( $post_id == 0 ) {
                        $post_params = array(
                            'post_type' => 'gsm-overstock',
                            'post_title' => $_POST[ 'sku' ],
                            'post_status' => 'publish'
                        );

                        $post_id = wp_insert_post( $post_params );
                    }
                }

                if ( $post_id ) {
                    $current_images = get_post_meta( $post_id, 'image', true );
                    if ( !$current_images ) {
                        $current_images = array();
                    }

                    if ( $all_files && count( $all_files ) ) {
                        $current_images = array_merge( $current_images, $all_files );
                    }

                    $response->current_images = $current_images;

                    update_post_meta( $post_id, 'sku', $_POST[ 'sku' ] );
                    update_post_meta( $post_id, 'price', number_format( $_POST[ 'price' ], 2 ) );
                    update_post_meta( $post_id, 'description', $_POST[ 'desc' ] );
                    update_post_meta( $post_id, 'unit_of_measure', $_POST[ 'um' ] );
                    update_post_meta( $post_id, 'quantity', $_POST[ 'quantity' ] );
                    update_post_meta( $post_id, 'length', $_POST[ 'length' ] );

                    update_post_meta( $post_id, 'image', $current_images );

                    $post_info = array(
                        'ID' => $post_id,
                        'post_title' => $_POST[ 'sku' ]
                    );

                    wp_update_post( $post_info );
                }
                break;
            case 'delete_overstock_image':
                if ( isset( $_POST[ 'post_id' ] ) ) {
                    $post_id = $_POST[ 'post_id' ];

                    $current_images = get_field( 'image', $post_id );

                    $response->current_images = $current_images;
                    $response->url = $_POST[ 'image' ];
                    if ( $current_images && count( $current_images ) ) {
                        $new_images = array();

                        foreach( $current_images as $image ) {
                            if ( $image != $_POST[ 'image' ] ) {
                                $new_images[] = $image;
                            }

                            update_post_meta( $post_id, 'image', $new_images );
                        }
                    }
                }
                break;
            case 'delete_overstock_item':
                if ( isset( $_POST[ 'post_id' ] ) ) {
                    wp_delete_post( $_POST[ 'post_id' ] );
                }
                break;
            case 'add_image_to_quote':
                $response->data = array();
                $quote_id = $_POST[ 'quote_id' ];

                $upload_dir = wp_upload_dir();
                if ( isset( $_FILES[ 'file' ] ) ) {
                    $our_file = $_FILES[ 'file' ];

                    $user_id = get_current_user_id();

                    $location = $upload_dir[ 'basedir' ] . '/drawings/' . (int)$user_id . '/';
                    @mkdir( $location, 0775, true );

                    $new_name = time() . '-' . $our_file[ 'name' ];
                    $new_file_name = $location . '/' . $new_name;
                    @unlink( $new_file_name );

                    rename( $our_file[ 'tmp_name' ], $new_file_name );
                    chmod( $new_file_name, 0644 );
                    $new_file_url = $upload_dir[ 'baseurl' ] . '/drawings/' . (int)$user_id . '/' . $new_name;

                    $response->url = $new_file_url;
                    $response->name = $new_name;
                    $response->success = 1;
                }

                break;

            case 'update_order_status':
                $post_id = $_POST[ 'id' ];
                $new_status = $_POST[ 'new_status' ];
                if ( $post_id ) {
                    update_post_meta( $post_id, 'order_status', $new_status );
                    gsm_update_save_data( $post_id, 'Order status changed to "' . $all_statuses[ $new_status ]  . '"' );
                }   
                break;
            case 'duplicate_pack':
                $pack_id = $_POST[ 'pack_id' ];
                $amount = $_POST[ 'amount' ];

                $current_items = get_field( 'items', $pack_id );
                $current_assigned = get_field( 'assigned_shipment', $pack_id );
                $current_pack_type = get_field( 'pack_type', $pack_id );

                for( $i = 0; $i < $amount; $i++ ) {
                    $new_pack = GSM_Packing_List::create_new_pack();

                    update_field( 'items', $current_items, $new_pack->post_id );
                    update_field( 'assigned_shipment', $current_assigned, $new_pack->post_id );
                    update_field( 'pack_type', $current_pack_type, $new_pack->post_id );
                        
                    wp_update_post(
                        array (
                            'ID'         => $new_pack->post_id,
                            'post_status' => 'publish'
                        )
                    );
                }

                break;
            case 'import_pack_order':
                $order_id = $_POST[ 'order_id' ];

                $order_data = get_post( $order_id );

                $response->po = get_post_meta( $order_id, 'gslp_po', true );
                $response->items = get_post_meta( $order_id, 'items', true );
                $response->order_id = $order_id;
                $response->post_data = $order_data;

                foreach( $response->items as $key => &$item ) {
                    $item[ 'has_profile_info' ] = 0;

                    if ( is_numeric( $item[ 'post_id'] ) ) {
                        $profile_info = gsm_get_one_moulding_info( $item[ 'post_id' ] );

                        if ( $profile_info ) {
                            $item[ 'info' ] = $profile_info;
                            $item[ 'has_profile_info' ] = 1;
                        } 
                    }

                    if ( isset( $item[ 'custom_sku'] ) && strlen( $item[ 'custom_sku' ] ) ) {
                        $item[ 'has_custom_sku' ] = 1;
                    } else {
                        $item[ 'has_custom_sku' ] = 0;
                    }
                }
                break;
            case 'get_quote_log':
                $quote_id = $_POST[ 'quote_id' ];

                $quote_log = get_post_meta( $quote_id, 'save_log', true );
                if ( !$quote_log ) {
                    $quote_log = array();
                } 

                $response->data = array_reverse( $quote_log );
                break;
            case 'quote_search':
                $response->data = array();
                
                $query_params = array(
                    'post_type' => 'gsm_quotations',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'showposts' => 1000
                );

                if ( isset( $_POST[ 'searchText' ] ) && $_POST[ 'searchText' ] ) {
                    $query_params[ 'meta_query' ] = array(
                        'relation' => 'OR',
                        'customer_name' => array(
                            'key' => 'customer_name',
                            'compare' => 'LIKE', 
                            'value' => $_POST[ 'searchText' ],
                            'type' => 'string'
                        ),
                        'contact_name' => array(
                            'key' => 'contact_name',
                            'compare' => 'LIKE', 
                            'value' => $_POST[ 'searchText' ],
                            'type' => 'string'
                        ),
                        'gslp_po' => array(
                            'key' => 'gslp_po',
                            'compare' => 'LIKE', 
                            'value' => $_POST[ 'searchText' ],
                            'type' => 'string'
                        ),
                         'cust_po' => array(
                            'key' => 'customer_po',
                            'compare' => 'LIKE', 
                            'value' => $_POST[ 'searchText' ],
                            'type' => 'string'
                        )
                    );

                    if ( isset( $_POST[ 'author' ] ) && $_POST[ 'author' ] ) {
                        $response->author = $_POST[ 'author' ];

                        $author = (int) $_POST[ 'author' ];
                        $query_params[ 'author' ] = $author;
                    }

                } else {
                    if ( isset( $_POST[ 'author' ] ) && $_POST[ 'author' ] ) {
                        $response->author = $_POST[ 'author' ];

                        $author = (int) $_POST[ 'author' ];
                        $query_params[ 'author' ] = $author;
                    }

                    if ( isset( $_POST[ 'period' ] ) && $_POST[ 'period' ] ) {
                        $response->period = $_POST[ 'period' ];

                        $period = (int) $_POST[ 'period' ];
                        
                        switch( $period ) {
                            case 1: 
                                $query_params[ 'date_query' ] = array(
                                    'column'  => 'post_date',
                                    'after'   => '-30 days'
                                );
                                break;
                            case 3: 
                                $query_params[ 'date_query' ] = array(
                                    'column'  => 'post_date',
                                    'after'   => '-90 days'
                                );
                                break;
                            case 6:
                                $query_params[ 'date_query' ] = array(
                                    'column'  => 'post_date',
                                    'after'   => '-183 days'
                                );
                                break;
                            case 12:
                                $query_params[ 'date_query' ] = array(
                                    'column'  => 'post_date',
                                    'after'   => '-365 days'
                                );
                                break;
                        }
                    }

                    if ( isset( $_POST[ 'company' ] ) && $_POST[ 'company' ] ) {
                        $response->company = $_POST[ 'company' ];

                        $author = (int) $_POST[ 'author' ];
                        $query_params[ 'meta_query' ] = array(
                            array(
                                'key' => 'customer_name',
                                'compare' => '=', 
                                'value' => $response->company,
                                'type' => 'string'
                            )
                        );
                    }

                    if ( isset( $_POST[ 'branch' ] ) && $_POST[ 'branch' ] ) {
                        $response->branch = $_POST[ 'branch' ];

                        $query = array(
                            'key' => 'gslp_location',
                            'compare' => '=', 
                            'value' => $response->branch,
                            'type' => 'string'
                        );

                        if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                            $query_params[ 'meta_query' ][] = $query;
                        } else {
                            $query_params[ 'meta_query' ] = array(
                                $query
                            );
                        }
                    }

                    if ( isset( $_POST[ 'type' ] ) && $_POST[ 'type' ] ) {
                        switch( $_POST[ 'type' ] ) {
                            case 'order':
                                $this_query = array(
                                    'key' => 'is_order',
                                    'compare' => '=',
                                    'value' => 1
                                );
                                break;
                            case 'quote':
                                $this_query = array(
                                    'relation' => 'OR',
                                    'order_clause' => array(
                                        'key' => 'is_order',
                                        'compare' => '=',
                                        'value' => array( 0 )
                                    ),
                                    'check_clause' => array(
                                        'key' => 'is_order',
                                        'compare' => 'NOT EXISTS'
                                    )
                                );
                                break;
                        }
                    
                        if ( is_array( $query_params[ 'meta_query' ] ) ) {
                            $query_params[ 'meta_query' ][] = $this_query;
                        } else {
                            $query_params[ 'meta_query' ] = array( $this_query );
                        }
                    }
                }


                $query = new WP_Query( 
                    $query_params
                );

                $all_post_statuses = gsm_get_all_order_statuses();
                while ( $query->have_posts() ) {
                    $query->the_post();

                    $item = new stdClass;
                    $item->num = get_the_ID();
                    $item->date = get_the_time( 'M j, Y' );
                    $item->sales_person = get_the_author();
                    $item->cust_po = get_field( 'customer_po' );
                    $item->gslp_po = get_field( 'gslp_po' );
                    $item->location = get_field( 'gslp_location' );
                    $item->company = get_field( 'customer_name' );
                    $item->contact_name = get_field( 'contact_name' );
                    $item->open_url = GSM_QUOTE_URL . '/?gsm_quote_id=' . $item->num;

                    $item->order_status = get_post_meta( get_the_ID(), 'order_status', true );
                    if ( !$item->order_status ) {
                        $item->order_status = 'default';
                    }

                    $is_order = get_field( 'is_order' );
                    $item->type_text = ( $is_order == '1' ? 'ORDER' : 'QUOTE' );

                    $item->order_status_friendly = $all_post_statuses[ $item->order_status ];

                    $quote_data = gsm_load_quote_data( $item->num );
                    $pricing = gsm_calculate_quote_pricing( $quote_data, $item->num, false );
                    $item->total_price = number_format( $pricing->price, 2 );

                    $response->data[] = $item;
                }

                break;
            case 'save_packing_list':
                $response->data = array();

                $post_id = $_POST[ 'post_id' ];
                $items = $_POST[ 'items' ];
                $updated_property = true;

                if ( $post_id ) {
                    update_field( 'pack_type', $_POST[ 'pack_type' ], $post_id );
                    $updated_property = update_field( 'items', $_POST[ 'items' ], $post_id );
            
                    wp_update_post(
                        array (
                            'ID'         => $post_id,
                            'post_status' => 'publish'
                        )
                    );
                }

                $response->post_id = $post_id;
                $response->update = $updated_property;
                $response->items = $_POST[ 'items' ];
                $response->saved_items = get_field( 'items', $post_id );

                break;
            case 'update_shipment_status':
                $response->new_status = $_POST[ 'new_status' ];
                $response->post_id = $_POST[ 'post_id' ];

                if ( $response->new_status && $response->post_id ) {
                    update_field( 'shipment_status', $response->new_status, $response->post_id );
                }
                break;
            case 'pack_change_assigned_shipment':
                $response->pack_id = $_POST[ 'pack_id' ];
                $response->shipment_id = $_POST[ 'assigned_shipment' ];

                update_field( 'assigned_shipment', $response->shipment_id, $response->pack_id ); 
                break;
            case 'get_single_ship_list':
                $response->data = array();

                $query_params = array(
                    'post_type' => 'gsm-shipments',
                    'post_status' => 'publish',
                    'p' => $_POST[ 'post_id' ]
                );

                $query = new WP_Query( $query_params );
                if ( $query->have_posts() ) {
                    $query->the_post();

                    $response->num = get_the_ID();
                    $response->ship_no = get_the_title();
                    $response->ship_to = get_field( 'destination' );
                    $response->created = get_the_time( 'M j, Y' );
                    $response->shipment_status = get_field( 'shipment_status' );

                    $packs = get_field( 'packs' );

                    if ( $packs ) {
                        $response->contained_packs = $packs;
                    } else {
                        $response->contained_packs = array();
                    }
                    

                    GSM_Packing_List::augment_ship_pack_list( $response->contained_packs );

                    /*
                    foreach( $packs as $one_pack ) {
                        $new_item = array();

                        $new_item[ 'pack_id' ] = $one_pack[ 'pack_id' ];
                        $new_item[ 'pack_type' ] = get_field( 'pack_type', $one_pack[ 'pack_id' ] );
                        $new_item[ 'friendly_pack_type' ] = GSM_Packing_List::get_friendly_pack_type( $new_item[ 'pack_type' ] );

                        if ( $new_item[ 'pack_type' ] == 'stock' ) {
                            $new_item[ 'pack_po' ] = $one_pack[ 'pack_po' ];
                        } else {
                            $all_pos = GSM_Packing_List::get_all_pos_within_pack( $one_pack[ 'pack_id' ] );
                            if ( $all_pos ) {
                                $new_item[ 'pack_po' ] = implode( ', ' , $all_pos );
                            } else {
                                $new_item[ 'pack_po' ] = '';
                            }
                        }

                        $new_item[ 'pack_info' ] = GSM_Packing_List::load_one_pack( $one_pack[ 'pack_id' ] );

                        $response->contained_packs[] = $new_item;
                    }  
                    */
                    
                }

                break;
            case 'get_single_packing_list':
                $response->data = array();

                $query_params = array(
                    'post_type' => 'gsm-packing-list',
                    'post_status' => 'publish',
                    'p' => $_POST[ 'post_id' ]
                );

                $query = new WP_Query( $query_params );
                if ( $query->have_posts() ) {
                    $query->the_post();

                    $response->data = get_field( 'items' );
                    $response->pack_no = get_the_title();
                    $response->pack_type = get_field( 'pack_type' );
                  
                    $response->friendly_pack_type = GSM_Packing_List::get_friendly_pack_type( $pack_type );
            
                    $response->created = get_the_time( 'M j, Y' );
                }

                break;
            case 'delete_shipping_list':
                if ( isset( $_POST[ 'post_id' ] ) ) {
                    $post_id = $_POST[ 'post_id' ];
                    wp_delete_post( $post_id );
                }
                break;
            case 'delete_packing_list':
                if ( isset( $_POST[ 'post_id' ] ) ) {
                    $post_id = $_POST[ 'post_id' ];
                    wp_delete_post( $post_id );
                }
                break;    
            case 'save_shipping_list':
                if ( isset( $_POST[ 'post_id' ] ) ) {
                    $post_id = $_POST[ 'post_id' ];
                    $query_params = array(
                        'post_status' => 'publish',
                        'ID' => $post_id
                    );

                    wp_update_post( $query_params );

                    update_field( 'shipment_status', $_POST[ 'ship_status' ], $post_id );
                    update_field( 'destination', $_POST[ 'ship_to' ], $post_id );
                }

                break;
            case 'get_shipping_lists':
                $response->data = array();

                $query_params = array(
                    'post_type' => 'gsm-shipments',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'showposts' => 1000
                );

                if ( isset( $_POST[ 'period' ] ) && $_POST[ 'period' ] ) {
                    $response->period = $_POST[ 'period' ];

                    $period = (int) $_POST[ 'period' ];
                    
                    switch( $period ) {
                        case 1: 
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-30 days'
                            );
                            break;
                        case 3: 
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-90 days'
                            );
                            break;
                        case 6:
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-183 days'
                            );
                            break;
                        case 12:
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-365 days'
                            );
                            break;
                    }
                }

                if ( isset( $_POST[ 'status' ] ) && $_POST[ 'status' ] ) {
                    $response->status = $_POST[ 'status' ];

                    switch( $_POST[ 'status' ] ) {
                        case 'pending':
                            $query = array(
                                'relation' => 'OR', 
                                array(
                                    'relation' => 'AND', 
                                    array(
                                        'key' => 'shipment_status',
                                        'compare' => 'EXISTS' 
                                    ),
                                    array(
                                        'key' => 'shipment_status',
                                        'compare' => '=', 
                                        'value' => 'pending',
                                    )
                                ),
                                array(
                                    'key' => 'shipment_status',
                                    'compare' => 'NOT EXISTS' 
                                ) 
                            );
                            break;
                        case 'shipped':
                            $query = array(
                                'relation' => 'AND', 
                                array(
                                    'key' => 'shipment_status',
                                    'compare' => 'EXISTS' 
                                ),
                                array(
                                    'key' => 'shipment_status',
                                    'compare' => '=', 
                                    'value' => 'shipped',
                                )
                            );
                            break;
                    }    

                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                }

                if ( isset( $_POST[ 'branch' ] ) && $_POST[ 'branch' ] ) {
                    $response->destination = $_POST[ 'branch' ];

                    switch( $_POST[ 'branch' ] ) {
                        case 'SC':
                            $query = array(
                                'relation' => 'OR', 
                                array(
                                    'relation' => 'AND', 
                                    array(
                                        'key' => 'destination',
                                        'compare' => 'EXISTS' 
                                    ),
                                    array(
                                        'key' => 'destination',
                                        'compare' => '=', 
                                        'value' => 'SC',
                                    )
                                ),
                                array(
                                    'key' => 'destination',
                                    'compare' => 'NOT EXISTS' 
                                ) 
                            );
                            break;
                        case 'NJ':
                            $query = array(
                                'relation' => 'AND', 
                                array(
                                    'key' => 'destination',
                                    'compare' => 'EXISTS' 
                                ),
                                array(
                                    'key' => 'destination',
                                    'compare' => '=', 
                                    'value' => 'NJ',
                                )
                            );
                            break;
                        case 'custom':
                            $query = array(
                                'relation' => 'AND', 
                                array(
                                    'key' => 'destination',
                                    'compare' => 'EXISTS' 
                                ),
                                array(
                                    'key' => 'destination',
                                    'compare' => '=', 
                                    'value' => 'custom',
                                )
                            );
                    }    

                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                }
                /*
                if ( isset( $_POST[ 'status' ] ) && $_POST[ 'status' ] ) {
                    $response->status = $_POST[ 'status' ];

                    switch( $_POST[ 'status' ] ) {
                        case 'unassigned':
                            $query = array(
                                'relation' => 'OR', 
                                array(
                                    'relation' => 'AND', 
                                    array(
                                        'key' => 'assigned_shipment',
                                        'compare' => 'EXISTS' 
                                    ),
                                    array(
                                        'key' => 'assigned_shipment',
                                        'compare' => '=', 
                                        'value' => 0,
                                    )
                                ),
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => 'NOT EXISTS' 
                                ) 
                            );
                            break;
                        case 'assigned':
                            $query = array(
                                'relation' => 'AND', 
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => 'EXISTS' 
                                ),
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => '>', 
                                    'value' => 0,
                                )
                            );
                            break;
                        case 'shipped':
                            // TBD
                            break;
                    }    


                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                }
                                    */

                /*
                if ( isset( $_POST[ 'type' ] ) && $_POST[ 'type' ] ) {
                    switch( $_POST[ 'type' ] ) {
                        case 'order':
                            $this_query = array(
                                'key' => 'is_order',
                                'compare' => '=',
                                'value' => 1
                            );
                            break;
                        case 'quote':
                            $this_query = array(
                                'relation' => 'OR',
                                'order_clause' => array(
                                    'key' => 'is_order',
                                    'compare' => '=',
                                    'value' => array( 0 )
                                ),
                                'check_clause' => array(
                                    'key' => 'is_order',
                                    'compare' => 'NOT EXISTS'
                                )
                            );
                            break;
                    }
                  
                    if ( is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $this_query;
                    } else {
                        $query_params[ 'meta_query' ] = array( $this_query );
                    }

                }
                                    */

                $query = new WP_Query( 
                    $query_params
                );

                $all_post_statuses = gsm_get_all_order_statuses();
                while ( $query->have_posts() ) {
                    $query->the_post();

                    $item = new stdClass;
                    $item->num = get_the_ID();
                    $item->date = get_the_time( 'M j, Y' );
                    $item->title = get_the_title();
                    $item->contact = get_field( 'contact_name' );
                    $item->destination = get_field( 'destination' );

                    $item->shipment_status = get_field( 'shipment_status' );
                    $item->packs = get_field( 'packs' );

                    $item->packs_friendly = "";
                    $item->included_skus = "";
                    $item->included_pos = "";

                    $item->total_length = 0;
                    $item->total_pieces = 0;

                    $item->some_packs = array();
                    if ( $item->packs && count( $item->packs ) ) {
                        $included_packs = array();
                        $included_skus = array();
                        $included_pos = array();

                        foreach( $item->packs as $one_pack ) {
                            $included_packs[] = get_the_title( $one_pack[ 'pack_id' ] );

                            $loaded_pack = GSM_Packing_List::load_one_pack( $one_pack[ 'pack_id' ] );

                            $item->some_packs[] = $loaded_pack;

                            $item->total_length += $loaded_pack->total_length;
                            $item->total_pieces += $loaded_pack->total_pieces;

                            if ( $loaded_pack->items ) {
                                foreach( $loaded_pack->items as $one_item ) {
                                    $included_skus = array_merge( $included_skus, array( get_the_title( $one_item[ 'packed_sku' ] ) ) );

                                    if ( trim( $one_item[ 'po' ] ) ) {
                                        $included_pos = array_merge( $included_pos, array( $one_item[ 'po' ] ) ) ;
                                    }
                                }
                            }

                            if ( $one_pack[ 'pack_po' ] ) {
                                $included_pos = array_merge( $included_pos, array( $one_pack[ 'pack_po' ] ) );
                            }
                        }

                        sort( $included_pos );
                        sort( $included_skus );
                        sort( $included_packs );
                        
                        $item->included_pos = implode( ", ", array_unique( $included_pos ) );
                        $item->included_skus = implode( ", ", array_unique( $included_skus ) );
                        $item->packs_friendly = implode( ", ", $included_packs );
                    }

                    $item->included_packs = $included_packs;
                    $response->data[] = $item;
                }         
                break;
            case 'quick_quote_sku':
                $response->post_id = $_POST[ 'post_id' ];

                $post_info = gsm_get_one_moulding_info( $response->post_id );
                $response->info = $post_info;
                $response->thickness = $post_info->thickness;
                $response->width = $post_info->width;
                $response->ripsku = $post_info->ripsku;
                break;
            case 'get_new_shipment_id':
                $response->post_id = 0;

                $query_params = array(
                    'post_type' => 'gsm-shipments',
                    'post_status' => 'draft'
                );

                $response->post_id = wp_insert_post( $query_params );
                $response->ship_no = 'SH' . date( 'ym' ) . '-' . $response->post_id;
                $response->created = date( 'F jS, Y' );

                $post_data = array(
                    'post_title' => $response->ship_no,
                    'ID' => $response->post_id
                );

                wp_update_post( $post_data );

                break;
            case 'get_new_pack_id':
                $response->post_id = 0;

                $new_response = GSM_Packing_List::create_new_pack();

                $response->post_id = $new_response->post_id;
                $response->pack_no = $new_response->pack_no;
                $response->created = $new_response->created;

                break;
            case 'remove_packs_from_shipment':
                $post_id = $_POST[ 'post_id' ];
                $packs = $_POST[ 'packs' ];
                if ( $post_id ) {
                    $response->post_id = $post_id;
                    $response->packs_to_delete = $packs;
                    $response->before_packs = get_field( 'packs', $post_id );
                    $response->after_packs = array();

                    foreach( $packs as $pack ) {
                        // assign pack to shipment
                        update_field( 'assigned_shipment', 0, $pack );
                    }

                    foreach( $response->before_packs as $one_pack ) {
                        if ( !in_array( $one_pack[ 'pack_id' ], $packs ) ) {
                            $response->after_packs[] = $one_pack;
                        }
                    }

                    update_field( 'packs', $response->after_packs, $post_id );

                    GSM_Packing_List::augment_ship_pack_list( $response->after_packs );
                }
                break;
            case 'add_pack_to_shipment':
                $post_id = $_POST[ 'post_id' ];
                $pack_po = $_POST[ 'po_to_assign' ];
                $packs = $_POST[ 'packs' ];
                if ( $post_id ) {
                    $response->post_id = $post_id;
                    $response->before_packs = get_field( 'packs', $post_id );

                    $response->after_packs = $response->before_packs;
                    if ( !$response->after_packs ) {
                        $response->after_packs = array();
                    }

                    foreach( $packs as $pack ) {
                        $new_item = array();

                        $new_item[ 'pack_id' ] = $pack;
                        $pack_type = get_field( 'pack_type', $pack );

                        if ( $pack_type == 'stock' ) {
                            $new_item[ 'pack_po' ] = $pack_po;
                        }

                        $response->after_packs[] = $new_item;

                        // assign pack to shipment
                        update_field( 'assigned_shipment', $post_id, $pack );
                    }

                    update_field( 'packs', $response->after_packs, $post_id );

                    GSM_Packing_List::augment_ship_pack_list( $response->after_packs );
                }
                break;
            case 'get_packing_lists':
                $response->data = array();

                $query_params = array(
                    'post_type' => 'gsm-packing-list',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'showposts' => 1000
                );

                if ( isset( $_POST[ 'period' ] ) && $_POST[ 'period' ] ) {
                    $response->period = $_POST[ 'period' ];

                    $period = (int) $_POST[ 'period' ];
                    
                    switch( $period ) {
                        case 1: 
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-30 days'
                            );
                            break;
                        case 3: 
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-90 days'
                            );
                            break;
                        case 6:
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-183 days'
                            );
                            break;
                        case 12:
                            $query_params[ 'date_query' ] = array(
                                'column'  => 'post_date',
                                'after'   => '-365 days'
                            );
                            break;
                    }
                }

                if ( isset( $_POST[ 'pack_type' ] ) && $_POST[ 'pack_type' ] ) {
                    $response->pack_type = $_POST[ 'pack_type' ];

                    $query = array(
                        'key' => 'pack_type',
                        'compare' => '=', 
                        'value' => $response->pack_type,
                        'type' => 'string'
                    );

                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                }

                if ( isset( $_POST[ 'contains' ] ) && $_POST[ 'contains' ] ) {
                    /*
                    $response->contains = $_POST[ 'contains' ];

                    $query = array(
                        'relation' => 'AND', 
                        array(
                            'key' => 'items',
                            'compare' => 'LIKE' 
                        ),
                        array(
                            'key' => 'items',
                            'compare' => 'LIKE', 
                            'value' => '"' . $response->contains . '"',
                        )
                    );

                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                    */
                }

                if ( isset( $_POST[ 'status' ] ) && $_POST[ 'status' ] ) {
                    $response->status = $_POST[ 'status' ];

                    switch( $_POST[ 'status' ] ) {
                        case 'unassigned':
                            $query = array(
                                'relation' => 'OR', 
                                array(
                                    'relation' => 'AND', 
                                    array(
                                        'key' => 'assigned_shipment',
                                        'compare' => 'EXISTS' 
                                    ),
                                    array(
                                        'key' => 'assigned_shipment',
                                        'compare' => '=', 
                                        'value' => 0,
                                    )
                                ),
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => 'NOT EXISTS' 
                                ) 
                            );
                            break;
                        case 'assigned':
                            $query = array(
                                'relation' => 'AND', 
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => 'EXISTS' 
                                ),
                                array(
                                    'key' => 'assigned_shipment',
                                    'compare' => '>', 
                                    'value' => 0,
                                )
                            );
                            break;
                        case 'shipped':
                            // TBD
                            break;
                    }    

                    if ( isset( $query_params[ 'meta_query' ] ) && is_array( $query_params[ 'meta_query' ] ) ) {
                        $query_params[ 'meta_query' ][] = $query;
                    } else {
                        $query_params[ 'meta_query' ] = array(
                            $query
                        );
                    }
                }
                        /*
                if ( is_array( $query_params[ 'meta_query' ] ) ) {
                    $query_params[ 'meta_query' ][] = $this_query;
                } else {
                    $query_params[ 'meta_query' ] = array( $this_query );
                }
                */

                $query = new WP_Query( 
                    $query_params
                );

                $all_post_statuses = gsm_get_all_order_statuses();
                while ( $query->have_posts() ) {
                    $query->the_post();

                    $item = new stdClass;
                    $item->num = get_the_ID();
                    $item->date = get_the_time( 'M j, Y' );
                    $item->title = get_the_title();
                    $item->status = 'unassigned';
                   
                    $item->pack_type = get_field( 'pack_type' );
                 //   $item = GSM_Packing_List::update_defaults( $item );
                    $item->friendly_pack_type = GSM_Packing_List::get_friendly_pack_type( $item->pack_type  );

                    $item->assigned_shipment = get_field( 'assigned_shipment' );
                    $item->has_shipped = false;

                    if ( !is_numeric( $item->assigned_shipment ) ) {
                        $item->assigned_shipment = 0;
                    } else {
                        // it's been assigned
                        $item->has_shipped = ( get_field( 'shipment_status', $item->assigned_shipment ) == 'shipped' );
                    }

                    $packed_items = get_field( 'items' );
                    $item->items = $packed_items;

                    GSM_Packing_List::cleanup_pack_items( $item->items );

                    $skus = array();
                    foreach( $packed_items as $one_sku ) {
                        $total_length = 0;

                        if ( $one_sku[ 'packed_sku' ] ) {
                            if ( is_numeric( $one_sku[ 'packed_sku'] ) ) {
                                $sku_name = get_the_title( $one_sku[ 'packed_sku' ] );
                            } else {
                                $sku_name = strtoupper( $one_sku[ 'packed_sku'] );
                            }
                            
                            if ( isset( $one_sku[ 'notes' ] ) && strlen( $one_sku[ 'notes' ] ) ) {
                                $sku_notes = $one_sku[ 'notes' ];
                            }
                            
  
                            $sku_obj = new stdClass;
                            $sku_obj->sku = $sku_name;
                            $sku_obj->length = 0;
                            $sku_obj->pcs = 0;
                            $sku_obj->notes = $sku_notes;
                            $sku_obj->po = $one_sku[ 'po' ];

                            $total_length += intval( $one_sku[ 'length_6' ] ) * 6;
                            $total_length += intval( $one_sku[ 'length_7' ] ) * 7;
                            $total_length += intval( $one_sku[ 'length_8' ] ) * 8;
                            $total_length += intval( $one_sku[ 'length_9' ] ) * 9;
                            $total_length += intval( $one_sku[ 'length_10' ] ) * 10;

                            $total_length += intval( $one_sku[ 'length_11' ] ) * 11;
                            $total_length += intval( $one_sku[ 'length_12' ] ) * 12;
                            $total_length += intval( $one_sku[ 'length_13' ] ) * 13;
                            $total_length += intval( $one_sku[ 'length_14' ] ) * 14;
                            $total_length += intval( $one_sku[ 'length_15' ] ) * 15;
                            $total_length += intval( $one_sku[ 'length_16' ] ) * 16;

                            $sku_obj->pcs = intval( $one_sku[ 'length_6' ] ) +
                                            intval( $one_sku[ 'length_7' ] ) +
                                            intval( $one_sku[ 'length_8' ] ) +
                                            intval( $one_sku[ 'length_9' ] ) +
                                            intval( $one_sku[ 'length_10' ] ) +
                                            intval( $one_sku[ 'length_11' ] ) +
                                            intval( $one_sku[ 'length_12' ] ) +
                                            intval( $one_sku[ 'length_13' ] ) +
                                            intval( $one_sku[ 'length_14' ] ) +
                                            intval( $one_sku[ 'length_15' ] ) +
                                            intval( $one_sku[ 'length_16' ] );

                            if ( isset( $one_sku[ 'pcs' ] ) && isset( $one_sku[ 'len' ] ) ) {
                                $total_length += intval( $one_sku[ 'pcs' ] ) * floatval( $one_sku[ 'len' ] );
                            }

                            if ( isset( $one_sku[ 'pcs' ] ) ) {
                                $sku_obj->pcs += intval( $one_sku[ 'pcs' ] );
                            }
                            
                            $sku_obj->length += $total_length;
                            
                            $item->skus[] = $sku_obj;
                        }
                    }

                    $response->data[] = $item;
                }
                break;
            case 'customer_search':
                $count = 1;
                $response->data = array();

                $customer = $_POST[ 'customer' ];
                if ( $customer ) {
                    
                    /*
                            'customer_name',
        'contact_name',
        'customer_phone',
        'customer_city',
        'customer_email',
        */
                    $query = new WP_Query( 
                        array(
                            'post_type' => 'gsm_quotations',
                            'showposts' => -1,
                            'meta_query' => array(
                                'relation' => 'OR',
                                array(
                                    'key' => 'customer_name',
                                    'value' => $customer,
                                    'compare' => 'LIKE'
                                ),
                                array(
                                    'key' => 'customer_city',
                                    'value' => $customer,
                                    'compare' => 'LIKE'
                                ),
                                array(
                                    'key' => 'contact_name',
                                    'value' => $customer,
                                    'compare' => 'LIKE'
                                )
                            )
                        )
                    );

                    $response->sql = $sql_query;

                    if ( $query->have_posts() ) {
                        while( $query->have_posts() ) {
                            $query->the_post();

                            $one_item = new stdClass;
                            $one_item->num = $count++;
                            $one_item->customer_name = get_post_meta( get_the_ID(), 'customer_name', true );
                            $one_item->location = get_post_meta( get_the_ID(), 'customer_city', true );
                            $one_item->contact = get_post_meta( get_the_ID(), 'contact_name', true );
                            $one_item->email = get_post_meta( get_the_ID(), 'customer_email', true );
                            $one_item->phone = get_post_meta( get_the_ID(), 'customer_phone', true );

                            $response->data[] = $one_item;
                        }
                    }
                }
                break;
            case 'sku_search':
                $count = 1;
                $response->data = array();

                $sku = $_POST[ 'sku' ];
                $check_width = $_POST[ 'width' ];
                $check_thickness = $_POST[ 'thickness' ];

                if ( $sku || true ) {     
                    global $wpdb;
                
                    $sql_query = "
                    SELECT 
                        " . $wpdb->prefix . "posts.ID," . $wpdb->prefix . "posts.post_title," . $wpdb->prefix . "terms.name,
                        MAX(CASE WHEN " . $wpdb->prefix . "postmeta.meta_key = 'custom_keywords' THEN " . $wpdb->prefix . "postmeta.meta_value ELSE NULL END) AS keywords
                    FROM " . $wpdb->prefix . "posts 
                    LEFT JOIN " . $wpdb->prefix . "postmeta ON (" . $wpdb->prefix . "postmeta.post_ID = " . $wpdb->prefix . "posts.ID)," . $wpdb->prefix . "terms," . $wpdb->prefix . "term_relationships
                    WHERE " . $wpdb->prefix . "term_relationships.object_ID = " . $wpdb->prefix . "posts.ID 
                        AND " . $wpdb->prefix . "posts.post_status = 'publish' 
                        AND " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $wpdb->prefix . "terms.term_ID 
                        AND (" . $wpdb->prefix . "posts.post_type = 'gsm-mouldings' || " . $wpdb->prefix . "posts.post_type = 'gsm-custom-mouldings' ) 
                        AND 
                            (" . $wpdb->prefix . "posts.post_title LIKE '%" . $_POST[ 'sku' ] . "%' OR " .  
                                $wpdb->prefix . "terms.name LIKE '%" . $_POST[ 'sku' ] . "%' OR (" .  
                                $wpdb->prefix . "postmeta.meta_value LIKE '%" . $_POST[ 'sku' ] . "%' AND " . $wpdb->prefix . "postmeta.meta_key = 'custom_keywords')) 
                    GROUP BY " . $wpdb->prefix . "posts.ID," . $wpdb->prefix . "terms.name
                    ORDER BY " . $wpdb->prefix . "posts.post_title ASC LIMIT 1000
                    ";

                    $response->sql = $sql_query;

                    $rows = $wpdb->get_results( $sql_query );
                    if ( $rows ) {
                        foreach( $rows as $row ) {
                            $include_item = true;

                            if ( $check_width > 0 ) {
                                $this_width = get_field( 'moulding_width', $row->ID );
                                if ( $this_width != $check_width ) {
                                    $include_item = false;
                                }
                            }

                            if ( $check_thickness > 0 ) {
                                $this_thickness = get_field( 'moulding_thickness', $row->ID );
                                if ( $this_thickness != $check_thickness ) {
                                    $include_item = false;
                                }
                            }

                            if ( $include_item ) {
                                $one_item = new stdClass;
                                $one_item->id = $row->ID;
                                $one_item->num = $count++;
                                $one_item->sku = $row->post_title;
                                $one_item->keywords = $row->keywords;
                                $one_item->mill_drawing = get_field( 'mill_drawing', $row->ID );

                                if ( function_exists( 'gsm_get_one_moulding_info' ) ) {
                                    $profile = gsm_get_one_moulding_info( $row->ID );
                                    $one_item->size = gsm_spaces_to_dashes( $profile->friendly_thickness ) . '" x ' . gsm_spaces_to_dashes( $profile->friendly_width ) . '"';
                                    $one_item->width = $profile->width;
                                }

                                $terms = get_the_terms( get_the_ID(), 'gsm-moulding-profiles' );
                                $one_item->profile = '';
                                if ( $terms ) {
                                    $one_item->profile = html_entity_decode( $terms[0]->name );
                                }

                                 $response->data[] = $one_item;
                            } 
                        }

                       // $temp_array = ;
                        usort( $response->data, function( $a, $b ) {
                            if ( $a->width == $b->width ) {
                                return 0;
                            }

                            return ( $a->width < $b->width ) ? -1 : 1;
                        });

                       // $response->data = $temp_array;
                        
/*
                        
                        $query = new WP_Query( 
                            array(
                                'post_type' => 'gsm-mouldings',
                                'post_status' => 'publish',
                                'showposts' => 10000,
                                'orderby' => 'post_title',
                                'order' => 'ASC'
                            )
                        );

                        while ( $query->have_posts() ) {
                            $query->the_post();
    
                            $one_item = new stdClass;
                            $one_item->num = $count++;
                            $one_item->sku = get_the_title();
                            $one_item->mill_drawing = get_field( 'mill_drawing' );
    
                            if ( function_exists( 'gsm_get_one_moulding_info' ) ) {
                                $profile = gsm_get_one_moulding_info( get_the_ID() );
                                $one_item->size = gsm_spaces_to_dashes( $profile->friendly_thickness ) . '" x ' . gsm_spaces_to_dashes( $profile->friendly_width ) . '"';
                            }
    
                            $terms = get_the_terms( get_the_ID(), 'gsm-moulding-profiles' );
                            $one_item->profile = '';
                            if ( $terms ) {
                                $one_item->profile = html_entity_decode( $terms[0]->name );
                            }
    
                            $response->data[] = $one_item;
                        }
                        */
                    }
                }

                break;
            case 'quick_quote_ripsku':
                $response = gsm_get_ripsku_data( $_POST[ 'thickness' ], $_POST[ 'width' ] );
                break;
            case 'duplicate_quote':
                $quote_id = $_POST[ 'quote_id' ];

                $new_quote_id = gsm_duplicate_post( $quote_id );
                if ( $new_quote_id ) {
                    $response->url = get_home_url( null, GSM_QUOTE_URL . '?gsm_quote_id=' . $new_quote_id );
                }
                break;
            case 'approximate_ripsku':
                $response->material = $_POST[ 'material' ];
                $quote_id = $_POST[ 'quote_id' ];
                
                $response->thickness = $_POST[ 'thickness' ];
                $response->width = $_POST[ 'width' ];

                $active_pricing = gsm_get_active_pricing_for_quote_id( $quote_id );

                $species = $active_pricing->species;
                $response->selected_species = $species[ $_POST[ 'material' ] ];

                $thickness_array = gsm_get_ripsku_approx_array();

                $selected_thickness = false;
                foreach( $response->selected_species->thicknesses as $thickness ) {
                    if ( isset( $thickness_array[ $thickness ] ) ) {
                        if ( $response->thickness <= $thickness_array[ $thickness ] ) {
                            $selected_thickness = $thickness;
                        }
                    }

                    if ( $selected_thickness ) {
                        break;
                    }
                }

                $response->pricing_error = 0;
                if ( !$selected_thickness ) {
                    // there is no corresponding pricing
                    $response->pricing_error = 1;
                }

                $nearest = ( $response->width + 0.25 ) / 0.25;
                $nearest_frac = $nearest - floor( $nearest );
                $nearest_base = floor( $nearest );
                $scaled = ( $nearest_base + 1 ) * 0.25;

                if ( $nearest_frac == 0 ) {
                    $scaled = $nearest_base * 0.25;
                }

                $response->ripsku_thickness = $selected_thickness;
                $response->ripsku_width = $scaled;
                $response->nearest_base = $nearest_base;
                $response->nearest = $nearest;
                $response->rip_sku = $response->ripsku_thickness . 'RIP' . $response->ripsku_width;

                $response->rip_sku_factor = gsm_get_thickness_factor( $response->ripsku_thickness ) * $response->ripsku_width / 12.0;
                
                break;
            case 'update_pricing':
                $quote_id = $_POST[ 'quote_id' ];
                delete_post_meta( $quote_id, 'active_pricing' );
                delete_post_meta( $quote_id, 'active_config' );

                // New requirement from Allan - update all the items to be default
                $quote_items = get_post_meta( $quote_id, 'items', true );
                if ( $quote_items ) {
                    $new_quote_items = array();

                    foreach( $quote_items as $quote_item ) {
                        if ( $quote_item[ 'custom_behavior' ] == 'override-final' ) {
                            $quote_item[ 'custom_behavior' ] = 'default';
                        }
                        
                        $quote_item[ 'markup' ] = 'default' ;

                        $new_quote_items[] = $quote_item;
                    }

                    update_post_meta( $quote_id, 'items', $new_quote_items );

                    gsm_update_quote_to_current_time( $quote_id );
                }

                break;
            case 'lookup_sku':
                $sku = $_POST[ 'sku' ];

                $quote_id = $_POST[ 'quote_id' ];
                $active_pricing = gsm_get_active_pricing_for_quote_id( $quote_id );

                $response->info = gsm_get_one_moulding_info( $sku );
                $response->info->mill_drawing = get_field( 'mill_drawing', $sku );
                if ( gsm_is_normal_sku( $sku ) ) {
                    $response->info->pricing = gsm_get_all_ripsku_pricing( $response->info->ripsku, $active_pricing->species );
                }
                break;
            case 'send_quote_email':
                $quote_id = $_POST[ 'quote_id' ];
                if ( $quote_id ) {
                    $send_to_email = $_POST[ 'send_address' ];
                    $send_to_me = $_POST[ 'send_to_me' ];
                    $content = $_POST[ 'email_content' ];

                    $to_mill = ( $_POST[ 'is_mill' ] == 1 );
                    if ( $send_to_email ) {
                        $author_id = get_post_field( 'post_author', $quote_id );
                       // $author_id = get_current_user_id();

                        $response = gsm_send_pdf_to_cust_or_mill( $quote_id, $send_to_email, $response, $content, $author_id, $send_to_me, $to_mill );

                        if ( $to_mill ) {
                            gsm_update_save_data( $quote_id, 'Sent email to MILL at ' . $send_to_email );
                        } else {
                            gsm_update_save_data( $quote_id, 'Sent email to CUSTOMER at ' . $send_to_email );
                        }
                    }
                }
                break;
            case 'save_quote':
                $response->redirect = 0;

                $quote_fields = gsm_get_activate_quote_fields();

                foreach( $quote_fields as $field ) {
                    $$field = ( isset( $_POST[ $field ] ) ? stripslashes_deep( $_POST[ $field ] ) : null );
                }

                $post_id = 0;
                if ( isset( $_POST[ 'quote_id' ] ) ) {
                    $post_id = $_POST[ 'quote_id' ];
                }

                $new_post = false;
                if ( $post_id == 0 ) {
                    $new_post = true;

                    $post_params = array(
                        'post_type' => 'gsm_quotations',
                        'post_title' => $customer_name,
                        'post_status' => 'publish'
                    );

                    $post_id = wp_insert_post( $post_params );

                    $response->redirect = 1;
                    $response->redirect_url = add_query_arg( array( 'gsm_quote_id' => $post_id ), GSM_QUOTE_URL );
                } 
                // TODO: Make sure we can write this data due to author permissions
                
                $current_order_status = get_post_meta( $post_id, 'order_status', true );

                // Update post meta
                foreach( $quote_fields as $field ) {
                    update_post_meta( $post_id, $field, $$field );
                }

                if ( $post_id != 0 && !$new_post ) {
                    // Update the title
                    $post = get_post( $post_id );

                    $post->post_title = $customer_name;
                    wp_update_post( $post );

                    gsm_update_save_data( $post_id, 'Updated' );
                } else {
                    gsm_update_save_data( $post_id, 'Created' );
                }

                // Update post date if it's an order
                $is_order = get_post_meta( $post_id, 'is_order', true );
                if ( $is_order == 1 ) {
                    gsm_update_quote_to_current_time( $post_id );

                    if ( isset( $_POST[ 'order_status' ] ) ) {
                        if ( $current_order_status != $_POST[ 'order_status' ] ) {
                            $all_statuses = gsm_get_all_order_statuses();
                            gsm_update_save_data( $post_id, 'Order status changed to "' . $all_statuses[ $_POST[ 'order_status' ] ]  . '"' );
                        }
                    }
                }

                if ( isset( $_POST[ 'sales_person'] ) ) {
                    $author_id = get_post_field( 'post_author', $post_id );
                    if ( $author_id != $_POST[ 'sales_person' ] ) {
                        $arg = array(
                            'ID' => $post_id,
                            'post_author' => (int)$_POST[ 'sales_person' ],
                        );
                        
                        wp_update_post( $arg );
                        
                        $new_contact = get_user_by( 'ID', $_POST[ 'sales_person' ] );

                        gsm_update_save_data( $post_id, 'Contact person changed to ' . $new_contact->display_name );
                    }
                }
              
                break;
            case 'load_overstock':
                $response->data = GSM_Overstock::load_all_overstock(); 
                break;
            case 'load_overstock_item':
                $response->data = GSM_Overstock::load_one_overstock( $_POST[ 'post_id' ] );
                break;
        }

        $response->success = 1;
    }
    echo json_encode( $response );

    wp_die();
}

function gsm_get_mpdf_for_quote( $quote_id, $for_the_mill = false, $author_id = 0 ) {
    $quote_data = gsm_load_quote_data( $quote_id );
    $html_template = gsm_get_email_body_content( $quote_data, $quote_id, false, $for_the_mill, $author_id );
	$footerimage = get_bloginfo( "template_directory" );

    if ( $for_the_mill ) {
        $html_template = str_replace( array( 'Quotation', '{{quotestyle}}' ), array( 'GSM ' . gsm_get_quote_or_order( $quote_data ), 'color: #7C0D0E;' ), $html_template );
    } else {
        $html_template = str_replace( array( 'Quotation', '{{quotestyle}}' ), array( 'GSL ' . gsm_get_quote_or_order( $quote_data ), 'color: #355E3B;' ), $html_template );
    }

    require_once __DIR__ . '/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf( [
        'orientation' => 'L',
        'mode' => 'utf-8',
        'default_font_size' => 9,
        'format' => [400, 300]
    ]);

    $mpdf->setAutoBottomMargin = 'stretch';

    $style_info = file_get_contents( dirname( __FILE__ ) . '/pdf/style.css' );
    $html_template = str_replace( '</body>', '<style>' . $style_info . '</style></body>', $html_template );

    $mpdf->SetHTMLFooter( '<em class="left">Page {PAGENO} of {nbpg}</em><br><img src="' . get_bloginfo( 'template_directory' ) . '/images/pdf-footer.jpg" width="1000" height="112" />' ); 
    $mpdf->WriteHTML( $html_template, \Mpdf\HTMLParserMode::DEFAULT_MODE );

    // Let's do watermark
    if ( $for_the_mill ) {
        $mpdf->SetWatermarkText( 'FOR INTERNAL USE ONLY' );
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
    }

    return $mpdf;
}

function gsm_add_one_sku_value( $data, $value ) {
    $info = new stdClass;
    $info->code = $data;
    $info->value = $value;
    return $info;
}

/**
 * Favicon
 * http://realfavicongenerator.net/
 */
function yhd_favicon() { ?>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/favicon-16x16.png">
	<link rel="manifest" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/site.webmanifest">
	<link rel="mask-icon" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/favicon.ico">
	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="msapplication-config" content="<?php echo get_bloginfo('stylesheet_directory'); ?>/favicons/browserconfig.xml">
	<meta name="theme-color" content="#21232b">
<?php }
add_action('wp_head', 'yhd_favicon');

function gsm_get_email_text() {
    $emails = new stdClass;

    $quotes = get_field( 'quotes', 'option' );
    $orders = get_field( 'orders', 'option' );

    $emails->customer_quote = $quotes[ 'customer' ];
    $emails->customer_order = $orders[ 'customer' ];;
    $emails->mill_order = $orders[ 'mill' ];
    $emails->mill_quote = $quotes[ 'mill' ];

    return $emails;
}

function gsm_enqueue_scripts() {
	wp_enqueue_style( 'typekit', 'https://use.typekit.net/iaw8xit.css' );
    wp_enqueue_style( 'primary', get_bloginfo( 'template_directory' ) . '/dist/bundle.css', false, GSM_THEME_VER . time() );
	wp_enqueue_style( 'gsmillquote-style-custom', get_bloginfo( 'template_directory' ) . '/css/style-custom.css', false, GSM_THEME_VER . time() );
	wp_enqueue_style( 'font-awesome', get_bloginfo( 'template_directory' ) . '/fontawesome/css/all.css', false, GSM_THEME_VER . time() );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui', get_bloginfo( 'template_directory' ) . '/src/jquery-ui/jquery-ui.min.js', false, GSM_THEME_VER . time() );
    wp_enqueue_script( 'jquery-autocomplete', get_bloginfo( 'template_directory' ) . '/src/js/jquery.autocomplete.js', false, GSM_THEME_VER . time() );
    // Enqueue quotes general script
    wp_enqueue_script( 'quotes-general', get_bloginfo( 'template_directory' ) . '/src/js/quotes/general.js', false, GSM_THEME_VER . time() );
    // Enqueue quotes customer lookup script
    // wp_enqueue_script( 'quotes-customer-lookup', get_bloginfo( 'template_directory' ) . '/src/js/quotes/customer-lookup.js', false, GSM_THEME_VER . time() );
    wp_enqueue_script( 'primaryjs', get_bloginfo( 'template_directory' ) . '/src/js/custom.js', false, GSM_THEME_VER . time() );

    //wp_enqueue_script( 'primaryjs', get_bloginfo( 'template_directory' ) . '/dist/bundle.js', false, GSM_THEME_VER . time() );

    $params = array(
        'admin_url' => admin_url( 'admin-ajax.php' ),
        'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
        'quote_id' => -1,
        'all_species' => false,
        'default_markup' => 'default',
        'custom_thickness' => 0.375,
        'order_url' => GSM_QUOTE_URL,
        'custom_width' => 0.375,
        'config' => gsm_get_configuration(),
        'download_pdf' => add_query_arg( array( 'gsm_action' => 'download_pdf', 'nonce' => wp_create_nonce( 'quote' ) ) ),
        'download_mill_pdf' => add_query_arg( array( 'gsm_action' => 'download_mill_pdf', 'nonce' => wp_create_nonce( 'quote' ) ) ),
        'nonce' => wp_create_nonce( 'quote' ),
        'is_order' => 0,
        'emails' => gsm_get_email_text(),
        'qc_mill_markup' => sprintf( '%0.2f', get_field( 'quick_quote_mill_markup', 'options' ) ),
        'show_dual_email' => isset( $_GET[ 'send_emails' ] ) ? 1 : 0,
        'is_jefe' => current_user_can( 'manage_options' ) ? '1' : '0',
        'is_sales_manager' => current_user_can( 'is_sales_manager' ) ? '1' : '0'
    );

    $params[ 'real_config' ] = clone( $params[ 'config' ] );

    $all_species = false;

    if ( isset( $_GET[ 'gsm_quote_id' ] ) ) {
        $params[ 'quote_id' ] = $_GET[ 'gsm_quote_id' ];

        $quote_data = gsm_load_quote_data( $params[ 'quote_id' ] );

        $actual_is_order = get_post_meta( $params[ 'quote_id' ], 'is_order', true );
        if ( $actual_is_order ) {
            $params[ 'is_order' ] = 1;
        }

        $active_pricing = get_post_meta( $params[ 'quote_id' ], 'active_pricing', true );
        if ( $active_pricing ) {
            $all_species = $active_pricing->species;

            $params[ 'pricing_data' ] = $active_pricing->date;
        } 

        $active_config = get_post_meta( $params[ 'quote_id' ], 'active_config', true );
        if ( $active_config ) {
            // Let's check for tiers
            if ( !isset( $active_config->tiers ) ) {
                $active_config->tiers = gsm_get_global_tiers();
            }
            $params[ 'config' ] = $active_config;
        } 
    }
    
    $current_user_id = get_current_user_id();
    if ( $current_user_id ) {
        $location = get_field( 'custom_user_location', 'user_' . $current_user_id );
        if ( $location == 'GSM' ) {
            $location = 'NJ';
        }
        $params[ 'user_location' ] = $location;
    } else {
        $params[ 'user_location' ] = 'SC';
    }
    

    if ( !$all_species ) {
        $all_species = gsm_get_all_species();
    }
   
    $all = array();
    foreach( $all_species as $species ) {
        $all[] = $species->basename;
    }

    $params[ 'all_species' ] = implode( ' ', $all );

    wp_localize_script( 'primaryjs', 'GSM', $params );
}