// ***********************************
    // GENERAL FUNCTIONS
    // ***********************************

        var GSM = {
            quote_id: -1,
            all_species: "alder-superior ash cherry fj-poplar hard-maple poplar qtr-sawn-white-oak radiata-fj-pine radiata-pine red-oak sapele walnut white-oak",
            default_markup: "default",
            custom_thickness: 0.375,
            order_url: "/order",
            custom_width: 0.375,
            qc_mill_markup: 1.40,
            config: {
              clearcoat_cost: 0.95, 
              custom_setup_charge: 175,
              default_lead_time: 4,
              edge_glue_factor: 0.07,
              knife_charge: 55,
              labor_multiplier: 1.15,
              price_decimals: 2,
              primed_cost: 0.35,
              primed_matrix: {
                    1: 0.5,
                    2: 0.5,
                    3: 0.5,
                    4: 0.5,
                    5: 0.5,
                    6: 0.5,
                    7: 0.5,
                    8: 0.5,
                    9: 0.5,
                    10: 0.5,
                    11: 0.5,
                    12: 0.5
                },
                custom_thickness: 0.375,
                custom_width: 0.375,
                default_markup: "default",
            }
        };
   
    var gsNumberFormat = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
      
        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
        //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
      });
    
      function gsmLogMessage( message ) {
        if ( window.console && console.log ) {
            console.log( message );      
        }
    
        var debugWindow = jQuery( '#debug_window' );
        if ( debugWindow.length ) {
            debugWindow.val( debugWindow.val() + message + "\n" ); 
        }
    }
    
    function gsTruncateNumber( number, digits ) {
        return parseFloat( number.toFixed( digits ) );
    }
    
    function gsMakeNiceString( str ) {
        return str.replace( / /g, '-' ).toLowerCase();
    }
    
   function gsmAdminAjax( specificAction, additionalParams, callback ) {
       // alert( 'doing ajax ' + specificAction );
        var data = {
            'action': 'handle_ajax',
            'gsm_action': specificAction,
            'gsm_nonce': GSM.ajax_nonce
        };
    
        // Add our parameters to the primary AJAX ones
        for ( var key in additionalParams ) {
            if ( additionalParams.hasOwnProperty( key  )) {
                data[ key ] = additionalParams[ key ];
            }
        }	
    
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post( GSM.admin_url, data, function( response ) {
            callback( response );
        });
    }
 
    function gsCalculatePrimingCost( width ) {
        if ( typeof GSM.config.primed_matrix != undefined ) {
            var roundedWidth = Math.ceil( width );
    
            if ( roundedWidth > 12 ) {
                roundedWidth = 12;
            } else if ( roundedWidth < 1 ) {
                roundedWidth = 1;
            }
    
            return GSM.config.primed_matrix[ roundedWidth ];
        }
    }
    