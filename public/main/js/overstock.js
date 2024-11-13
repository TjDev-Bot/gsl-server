function gsmOverstockAjax( specificAction, additionalParams, callback ) {
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

function gsSetupOverstock() {
    if ( jQuery( '#overstock-list-area' ).length ) {
        var packing_list = new Vue({
            el: '#overstock-list-area',
            data: {
                results: [],

                modal: false,

                post_id: 0,
                sku: '',
                desc: 'test',
                um: 'lf',
                price: 0.00,
                length: 0,
                qty: 1,
                images: []
            },
            methods: {
                editOverstock( num ) {
                    var params = {
                        post_id: num
                    };

                    var context = this;
                    gsmOverstockAjax( 'load_overstock_item', params, function( result ) {
                        if ( result ) {
                            var decodedResult = jQuery.parseJSON( result );
                            
                            context.post_id = decodedResult.data.ID;
                            context.sku = decodedResult.data.sku;
                            context.desc = decodedResult.data.desc;
                            context.qty = decodedResult.data.quantity;
                            context.price = decodedResult.data.price;
                            context.um = decodedResult.data.unit_of_measure;
                            context.images = decodedResult.data.images;
                            context.length = decodedResult.data.length;

                            if ( context.modal ) {
                                context.clearFileUploadBox();

                                context.modal.show();
                            }
                        }
                    });
                },
                deleteOverstockItem( id, e ) {
                    if ( id && confirm( "Are you sure you wish to delete this item?" ) ) {
                        var params = {
                            post_id: id
                        };

                        var context = this;
                        gsmOverstockAjax( 'delete_overstock_item', params, function( result ) {
                          //  alert( result );

                            context.modal.hide();
                            context.updateData();
                        });
                    }
                 
                    e.preventDefault();
                },
                deleteImage( url, id, e ) {
                    if ( id && confirm( "Are you sure you want to delete this image?" ) ) {
                        var params = {
                            post_id: id,
                            image: url
                        };

                        var context = this;
                        gsmOverstockAjax( 'delete_overstock_image', params, function( result ) {
                          //  alert( result );

                            context.images = context.images.filter( function( item ) { return item != url; });

                            context.updateData();
                        });
                    }
                 
                    e.preventDefault();
                },
                updateData() {
                    var params = {};
                    var context = this;

                    gsmOverstockAjax( 'load_overstock', params, function( result ) {
                        if ( result ) {
                            var decodedResult = jQuery.parseJSON( result );
                            context.results = decodedResult.data;

                            context.$forceUpdate();
                        }
                    });
                },
                skuChanged() {
                },
                doOverstockSave() {
                    var formData = new FormData();

                    // This way formData is filled in with the fields/values from #formElement
                    var formData = new FormData( document.getElementById( "overstock_form" ) );
                    formData.append( "action", "handle_ajax" );
                    formData.append( "gsm_action", "save_overstock_items" );
                    formData.append( "gsm_nonce", GSM.ajax_nonce );

                    var context = this;

                    jQuery.ajax({
                        type: 'POST',
                        url: GSM.admin_url,
                        data: formData,
                        contentType : false,
					    processData : false,
                        success: function ( response ) {
                            //alert( response );
                            context.modal.hide();
                            context.updateData();
                        }
                    });
                },
                imageUpload( event ) {

                },
                clearFileUploadBox() {
                    var fileInput = jQuery( '#overstock_images' );
                    fileInput.replaceWith( fileInput.val('').clone(true) );
                }
            },
            mounted() {
                if ( !this.modal ) {
                    this.modal = new bootstrap.Modal( document.getElementById( 'overstock_dialog' ), { keyboard: true, backdrop: 'static' } );
                }

                var context = this;
                jQuery( '.add-overstock' ).click( function( e ) {
                    if ( context.modal ) {
                        context.post_id = 0;
                        context.images = [];
                        context.sku = '';
                        context.price = '1.00';
                        context.qty = 1;
                        context.desc = '';
                        context.unit_of_measure = 'lf';
                        context.length = 1;
                  
                        context.clearFileUploadBox();
                    
                        context.modal.show();
                    }

                    e.preventDefault();
                });

                this.updateData();
            }
        });
    }
}

jQuery( document ).ready( function() { 
        gsSetupOverstock(); 
    } 
);