 // ***********************************
    // CUSTOMER  LOOKUP
    // ***********************************

    if ( jQuery( '#customer_lookup_modal').length ) {
        var sku_lookup = new Vue({
            el: '#customer_lookup_modal',
            data: {
                search: '',
                results: [],
                timer: false,
                target: false
            },
            methods: {
                actualCustomerChange() {
                    var params = {
                        customer: this.search
                    }
            
                    var thisContext = this;

                    var oldText = jQuery( 'h6#search_customer_title' ).html();
                    jQuery( 'h6#search_customer_title' ).html( 'Loading, please wait...' );

                    jQuery( 'button#search_customer_button' ).prop( 'disabled', true );

                    gsmAdminAjax( 'customer_search', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;

                        jQuery( 'h6#search_customer_title' ).html( oldText );
                        jQuery( 'button#search_customer_button' ).prop( 'disabled', false );
                    });

                    this.timer = false;
                },
                onCustomerChange() {
                    if ( this.timer ) {
                        clearTimeout( this.timer );
                    }

                    this.timer = setTimeout( this.actualCustomerChange, 100 );   
                }
            },
            mounted() {
                this.search = '';

                var myModal = new bootstrap.Modal( document.getElementById( 'customer_lookup_modal' ) );

                jQuery( 'div.customer-search-icon a' ).click( function( e ) {
                    e.preventDefault();
                    
                    myModal.target = jQuery( this ).attr( 'data-target' );
                  //  alert( myModal.target );
                    myModal.show();
                });

                jQuery( '#customer_lookup_modal .table' ).on( 'click', 'a.update_customer', function ( e ) {
                    e.preventDefault();

                    var customer = jQuery( this ).attr( 'data-customer-name' );
                    var location = jQuery( this ).attr( 'data-location' );
                    var contact = jQuery( this ).attr( 'data-contact' );
                    var phone = jQuery( this ).attr( 'data-phone' );
                    var email = jQuery( this ).attr( 'data-email' );

                   // alert( myModal.target );
                    if( myModal.target == "billing" ) {
                        jQuery( '#customer_name' ).val( customer );
                        jQuery( '#city' ).val( location );
                        jQuery( '#contact_name' ).val( contact );
                        jQuery( '#phone' ).val( phone );
                        jQuery( '#email' ).val( email );
                        jQuery( '.save-quote' ).prop( 'disabled', false );
                    } else {
                        jQuery( '#customer_name_shipment' ).val( customer );
                        jQuery( '#city_shipment' ).val( location );
                        jQuery( '#contact_name_shipment' ).val( contact );
                        jQuery( '#phone_shipment' ).val( phone );
                    }

                    myModal.hide();
                });
            }
        });
    }