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

function gsSetupShippingList() {
    if ( jQuery( '#shipping_list_area' ).length ) {
        var packing_list = new Vue({
            el: '#shipping_list_area',
            data: {
                post_id: 0,
            
                // Main packing list dialog
                filter_status: 0,
                filter_contains: 0,
                filter_period: 0,
                filter_branch: 0,
                results: [],
                created: 0,
                ship_no: '',
                ship_to: 'SC',
                ship_street: '',
                ship_city: '',
                ship_contact: '',
                shipment_status: 0,
                contained_packs: [],

                // Pack adding
                modal: false,
                editing: false,

                // Pack Searching
                search_packs_modal: false,
                search_packs_filter: 'stock',
                po_to_assign: '',
                search_packs_results: [],

                // Hide event
                process_hide_event: false
            },
            methods: {
                onFilterStatusChange() { this.updateData(); },
                onFilterContainsChange() { this.updateData(); },
                onFilterBranchChange() { this.updateData(); },
                onFilterPeriodChange() { this.updateData(); },

                updateData() {
                    var params = {
                        period: this.filter_period,
                        status: this.filter_status,
                        pack_type: this.filter_pack_types,
                        branch: this.filter_branch
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'get_shipping_lists', params, function( response ) {
                        //alert( response );
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;
                       // jQuery( '#quote_search_modal h6' ).html( oldHtml );
                    });
                },
                addPack() {},
                doShipmentSave() {
                    var params = {
                        post_id: this.post_id,
                        ship_to: this.ship_to,
                        ship_street: this.ship_street,
                        ship_city: this.ship_city,
                        ship_contact: this.ship_contact,
                        ship_status: this.shipment_status
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'save_shipping_list', params, function( response ) {
                       // alert( response );
                        var decodedResponse = jQuery.parseJSON( response );

                        thisContext.updateData();
                        thisContext.process_hide_event = false;
                        thisContext.modal.hide();
                        thisContext.updateData();
                    });
                },
                removePacks() {
                },
                editShipment( post ) {
                    var params = {
                        post_id: post
                    }

                    this.post_id = post;

                    var vueObject = this;
                    gsmAdminAjax( 'get_single_ship_list', params, function( response ) {
                       //alert( response );
                        var decodedResponse = jQuery.parseJSON( response );

                       // vueObject.pack_type = decodedResponse.pack_type;
                        vueObject.created = decodedResponse.created;
                        vueObject.ship_no = decodedResponse.ship_no;
                        vueObject.ship_to = decodedResponse.ship_to;
                        vueObject.contained_packs = decodedResponse.contained_packs;
                        vueObject.shipment_status = decodedResponse.shipment_status;
                        vueObject.process_hide_event = true;

                        vueObject.editing = true;
                       // vueObject.add_edit_pack_data = decodedResponse.data;

                        if ( !vueObject.modal ) {
                            vueObject.modal = new bootstrap.Modal( document.getElementById( 'shipping_list_dialog' ), { keyboard: true, backdrop: 'static' } );
                        }

                        //vueObject.recomputeTable();
                        
                        vueObject.modal.show(); 
                    });
                },
                searchForPacks() {
                    if ( !this.search_packs_modal ) {
                        this.search_packs_modal = new bootstrap.Modal( document.getElementById( 'search_packs' ), { keyboard: true, backdrop: 'static' } );
                    }

                    this.search_packs_modal.show();
                    this.updateSearchPackDialog();
                },
                updateSearchPackDialog() {
                   // alert( 'update' );
                    var params = {
                        pack_type: this.search_packs_filter,
                        status: 'unassigned'
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'get_packing_lists', params, function( response ) {
                      //  alert( response );
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.search_packs_results = decodedResponse.data;

                        thisContext.$forceUpdate()
                    });

                    jQuery( 'tr.search_packs_row input[type=checkbox]' ).prop( 'checked', false );
                },
                discardChanges() {
                    //this.modal.hide();
                    
                },
                deleteShipment() { 
                    var params = {
                        post_id: this.post_id
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'delete_shipping_list', params, function( response ) {
                        thisContext.process_hide_event = false;
                        thisContext.modal.hide();
                        thisContext.updateData();
                    });
                },
                discardOrDelete() {
                    if ( this.process_hide_event ) {
                        if ( !this.editing ) {
                            this.deleteShipment();
                        } else {
                            this.discardChanges();
                        }
                    }
                },
                handleAddNewClose() {
                    this.discardOrDelete();
                    this.modal.hide();
                },
                closeShipmentModal() {
                    if ( this.search_packs_modal ) {
                        this.updateData();
                        this.process_hide_event = false; 
                        this.search_packs_modal.hide();
                    }
                },
                addSelectedPack() {
                    var items = [];
                    jQuery( 'table.pack-search-table tr td.selected' ).each( function() {
                        if ( jQuery( this ).find( 'input' ).is( ':checked' ) ) {
                            var post_ID = jQuery( this ).attr( 'data-num' );
                            items.push( post_ID );
                        }
                    });

                    var params = {
                        post_id: this.post_id,
                        po_to_assign: this.po_to_assign,
                        packs: items
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'add_pack_to_shipment', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                       // alert( response );
                      //  thisContext.contained_packs = decodedResponse.after_packs;
                       // thisContext.updateData();
                        thisContext.contained_packs = decodedResponse.after_packs;

                        thisContext.updateSearchPackDialog();
                    });
                    
                    //alert( items );
                },
                removePacks() {
                    var items = [];
                    jQuery( 'table.shipment-table tr td.selected' ).each( function() {
                        if ( jQuery( this ).find( 'input' ).is( ':checked' ) ) {
                            var post_ID = jQuery( this ).attr( 'data-num' );
                            items.push( post_ID );
                        }
                    });

                     var params = {
                        post_id: this.post_id,
                        packs: items
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'remove_packs_from_shipment', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                        //alert( response );
                        thisContext.contained_packs = decodedResponse.after_packs;

                        thisContext.updateSearchPackDialog();
                    });
                },
                updateTable() {
                },
                updateShipmentStatus( num, status ) {
                //    alert( num + ' ' + status );
                    var params = {
                        post_id: num,
                        new_status: status
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'update_shipment_status', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                        //alert( response );
                        thisContext.updateSearchPackDialog();
                    });
                }
            },
            mounted() {
                var vueObject = this;
                jQuery( '.add_new_shipment button' ).click( function( e ) {
                    var params = {
                    }

                    gsmAdminAjax( 'get_new_shipment_id', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                        vueObject.post_id = decodedResponse.post_id;
                        vueObject.ship_no = decodedResponse.ship_no;
                        vueObject.created = decodedResponse.created;
                        vueObject.contained_packs = [];
                        vueObject.shipment_status = 'pending';

                        vueObject.editing = false;

                        if ( !vueObject.modal ) {
                            vueObject.modal = new bootstrap.Modal( document.getElementById( 'shipping_list_dialog' ), { keyboard: true, backdrop: 'static' } );

                            var myModalEl = document.getElementById( 'shipping_list_dialog' )
                            myModalEl.addEventListener('hide.bs.modal', (event) => {
                                vueObject.discardOrDelete();
                            });
                        } 

                        vueObject.modal.show();
                    });

                    e.preventDefault();
                });

                this.updateData();
            }
        });
    }
}

function gsSetupPackingList() {
    if ( jQuery( '#packing-list-area' ).length ) {
        var packing_list = new Vue({
            el: '#packing-list-area',
            data: {
                modal: false,
                post_id: 0,
                pack_num: 0,
                filter_status: 0,
                filter_contains: 0,
                filter_period: 0,
                filter_pack_types: 0,
                results: [],
                add_edit_pack_data: [],
                pack_type: 'stock',
                created: 0,
                editing: false,
                process_hide_event: true,
                import_order_id: 0
            },
            methods: {
                onFilterStatusChange() { this.updateData(); },
                onFilterContainsChange() { this.updateData(); },
                onFilterPeriodChange() { this.updateData(); },
                onFilterPackTypesChange() { 
                    this.updateData(); 
                },

                updateStockNotes( sku, index ) {
                  //  alert( sku +  ' ' + index );
                    if ( sku ) {
                        var desc = jQuery( 'td.pack-sku select option[value=' + sku + ']' ).attr( 'data-desc' );
                        if ( desc.length ) {
                            this.add_edit_pack_data[ index ].notes = desc;
                            this.$forceUpdate();
                        }
                    }
                },

                updateData() {
                    var params = {
                        period: this.filter_period,
                        status: this.filter_status,
                        contains: this.filter_contains,
                        pack_type: this.filter_pack_types
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'get_packing_lists', params, function( response ) {
                       //alert( response );
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;

                       // jQuery( '#quote_search_modal h6' ).html( oldHtml );
                    });
                },
                checkCustom( index ) {
                    if ( this.pack_type == 'stock' ) {
                        var sku = this.add_edit_pack_data[ index ].packed_sku;
                        var option = jQuery( '#sku-list' ).find( 'option[value=' + sku + ']' );
                        if ( option.length ) {
                            if ( option.attr( 'data-contains-length' ) == '1' ) {
                                return true;
                            }   
                        }
                    } 

                    return false;
  
                },
                editPack( post ) {
                    var params = {
                        post_id: post
                    }

                    this.post_id = post;

                    var vueObject = this;
                    gsmAdminAjax( 'get_single_packing_list', params, function( response ) {
                        vueObject.editing = true;

                        var decodedResponse = jQuery.parseJSON( response );

                        vueObject.pack_num = decodedResponse.pack_no;
                        vueObject.pack_type = decodedResponse.pack_type;
                        vueObject.created = decodedResponse.created;
                        vueObject.add_edit_pack_data = decodedResponse.data;
                        vueObject.process_hide_event = true;

                        if ( !vueObject.modal ) {
                            vueObject.modal = new bootstrap.Modal( document.getElementById( 'packing_list_dialog' ), { keyboard: true, backdrop: 'static' } );
                        }

                        vueObject.recomputeTable();
                        
                        vueObject.modal.show(); 
                    });
                },
                changePackStatus() {
                },
                addNewLine() {
                    var newData = {
                        po: '',
                        packed_sku: 0,
                        desc: '',
                        pcs: 0,
                        len: 0,
                        length_6: 0,
                        length_7: 0,
                        length_8: 0,
                        length_9: 0,
                        length_10: 0,
                        length_11: 0,
                        length_12: 0,
                        length_13: 0,
                        length_14: 0,
                        length_15: 0,
                        length_16: 0,
                        total: 0,
                        pieces: 0,
                        other: 0,
                        notes: ''
                    }

                    this.add_edit_pack_data.push( newData );

                    this.recomputeTable();
                },
                clearAllSkus() {
                    this.add_edit_pack_data = [];
                    this.addNewLine();
                },
                deleteData(k) {
                    this.add_edit_pack_data.splice(k, 1)
                },
                convertToInt( item ) {
                    var newItem = parseInt( item );
                    if ( isNaN( newItem ) ) {
                        newItem = 0;
                    }

                    return newItem;
                },
                calculateItemLength( item ) {
                    return  this.convertToInt( item.length_6 )*6 + 
                            this.convertToInt( item.length_7 )*7 + 
                            this.convertToInt( item.length_8 )*8 + 
                            this.convertToInt( item.length_9 )*9 + 
                            this.convertToInt( item.length_10 )*10 + 
                            this.convertToInt( item.length_11 )*11 + 
                            this.convertToInt( item.length_12 )*12 +
                            this.convertToInt( item.length_13 )*13 + 
                            this.convertToInt( item.length_14 )*14 + 
                            this.convertToInt( item.length_15 )*15 + 
                            this.convertToInt( item.length_16 )*16 + 
                            this.convertToInt( item.pcs ) * this.convertToInt( item.len ) 
                },
                calculateItemPieces( item ) {
                    return this.convertToInt( item.length_6 ) + 
                           this.convertToInt( item.length_7 ) + 
                           this.convertToInt( item.length_8 ) + 
                           this.convertToInt( item.length_9 )  + 
                           this.convertToInt( item.length_10 )  + 
                           this.convertToInt( item.length_11 )  + 
                           this.convertToInt( item.length_12 )  +
                           this.convertToInt( item.length_13 )  + 
                           this.convertToInt( item.length_14 )  + 
                           this.convertToInt( item.length_15 )  + 
                           this.convertToInt( item.length_16 ) + 
                           this.convertToInt( item.pcs );
                },
                deletePackItem( deleteIndex ) {
                    var newData = [];
                    for ( let index = 0; index < this.add_edit_pack_data.length; index++) {
                        if ( index != deleteIndex ) {
                           // alert( index + " " + deleteIndex );
                            var post = this.add_edit_pack_data[ index ];
                            newData.push( post );
                        }
                    }

                    this.add_edit_pack_data = newData;

                    if ( this.add_edit_pack_data.length == 0 ) {
                        this.addNewLine();
                    }
                },
                recomputeTable() {
                    for (let index = 0; index < this.add_edit_pack_data.length; ++index) {
                        var element = this.add_edit_pack_data[index];
                        element.total = this.calculateItemLength( element );
                        element.pieces = this.calculateItemPieces( element );
                    }

                    // Needed to trick VUE
                    this.$forceUpdate();
                },
                changeAssignedShipment( num, new_shipment ) {
                    var params = {
                        pack_id: num,
                        assigned_shipment: new_shipment
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'pack_change_assigned_shipment', params, function( response ) {
                       // alert( response );
                      //  thisContext.updateData(); 
                    });
                //    alert( num + ' ' + new_shipment );

                 
                },
                doPackSave() {
                    var params = {
                        post_id: this.post_id,
                        pack_type: this.pack_type,
                        items: this.add_edit_pack_data
                    }

                    this.updateData();
                    var thisContext = this;

                   // alert( JSON.stringify( params ) );

                    gsmAdminAjax( 'save_packing_list', params, function( response ) {
                       // alert( 'savings ' + response );
                        thisContext.editing = true;
                        thisContext.process_hide_event = false;

                        thisContext.modal.hide();

                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.updateData();
                    });
                },
                discardChanges() {
                   // this.modal.hide();
                },
                deletePack( pack_id = 0, event = 0 ) { 
                    var params = {
                        
                    }

                    if ( event ) {
                        event.preventDefault();
                    }

                    if ( pack_id > 0 ) {
                        params.post_id = pack_id;
                    } else {
                        params.post_id = this.post_id
                    }

                    var thisContext = this;

                    //alert( 'deleting post ' + this.post_id );

                    gsmAdminAjax( 'delete_packing_list', params, function( response ) {
                      //  alert( response );
                        if ( pack_id == 0 ) {
                            thisContext.process_hide_event = false;
                            thisContext.modal.hide();
                        }

                        thisContext.updateData();
                    });
                },
                doPackDelete() {
                    this.deletePack();
                },
                discardOrDelete() {
                    if ( !this.editing ) {
                        this.deletePack();
                    } else {
                        this.discardChanges();
                    }
                },
                handleHideEvent() {
                    if ( this.process_hide_event ) {
                        this.discardOrDelete();
                    } 
                },
                hasPackEntries() {
                    if ( this.add_edit_pack_data.length == 0 ) { 
                        return false;
                    } else {          
                        var assigned = 0;
                        for ( let index = 0; index < this.add_edit_pack_data.length; ++index) {
                            if ( this.add_edit_pack_data[ index ].packed_sku != '0' ) { 
                          //      alert( this.add_edit_pack_data[ index ].packed_sku );
                                assigned = 1;
                            }
                        }

                        return ( assigned == 1 );
                    }
                },
                cleanupData() {
                    // Cleans up unassigned from imported data
                    var hasData = 0;
                    var hasUnassigned = 0;

                    if ( this.add_edit_pack_data.length ) {
                        for ( let index = 0; index < this.add_edit_pack_data.length; ++index) {
                            var post = this.add_edit_pack_data[ index ];

                            if ( post.packed_sku != 0 ) {
                                hasData = 1;
                            } else {
                                hasUnassigned = 1;
                            }
                        }

                        if ( hasData && hasUnassigned ) {   
                            var newData = [];
                            for ( let index = 0; index < this.add_edit_pack_data.length; ++index) {
                                var post = this.add_edit_pack_data[ index ];

                                if ( post.packed_sku != 0 ) {
                                    newData.push( post );
                                }
                            }

                            this.add_edit_pack_data = newData;
                        }
                    }
                },
                duplicatePack( packNum, num, event ) {
                    var params = {
                        pack_id: packNum,
                        amount: num
                    }

                    event.preventDefault();

                    var thisContext = this;
                    gsmAdminAjax( 'duplicate_pack', params, function( response ) {
                        thisContext.updateData();
                    });
                },
                importPackOrder() {
                   // alert( this.import_order_id );

                    var params = {
                        order_id: this.import_order_id
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'import_pack_order', params, function( response ) {
                      //  alert( response );
                        var decodedResponse = jQuery.parseJSON( response );

                        for ( let index = 0; index < decodedResponse.items.length; ++index) {
                            var post = decodedResponse.items[ index ];

                         //   alert( post.post_id );

                            var newData = {
                                po: decodedResponse.po,
                                packed_sku: post.post_id,
                                desc: '',
                                pcs: 0,
                                len: 0,
                                length_6: 0,
                                length_7: 0,
                                length_8: 0,
                                length_9: 0,
                                length_10: 0,
                                length_11: 0,
                                length_12: 0,
                                length_13: 0,
                                length_14: 0,
                                length_15: 0,
                                length_16: 0,
                                total: 0,
                                pieces: 0,
                                other: 0,
                                notes: ''
                            }

                            if ( post.has_custom_sku == 1) {
                                newData.notes = newData.notes + post.custom_sku + " - ";
                            }

                            if ( post.post_id == 's4s' || post.post_id == 'custom' ) {
                                newData.notes = newData.notes + post.custom_thickness_friendly + '"x' + post.custom_width_friendly + '"' + ' - ';
                            } else {
                                if ( post.has_profile_info == 1 ) {
                                    newData.notes = newData.notes + post.info.friendly_thickness + '"x' + post.info.friendly_width + ' - ' + post.info.profile + ' - ';
                                }
                            }

                            newData.notes = newData.notes + post.species.toUpperCase()
                            if ( post.finish != 'unfinished' ) {
                                newData.notes = newData.notes + ' - ' + post.finish.toUpperCase();
                            }

                            newData.notes = newData.notes.toUpperCase();

                            thisContext.add_edit_pack_data.push( newData );
                          //  alert( JSON.stringify( post ) );
                        }

                        thisContext.cleanupData();

                        thisContext.recomputeTable();
                        //thisContext.results = decodedResponse.data;
                    });
                }
            },
   

            mounted() {
                var vueObject = this;
                jQuery( '.add-new-pak button' ).click( function( e ) {
                    e.preventDefault();

                    var params = {
                    }

                    var thisContext = vueObject;
                    gsmAdminAjax( 'get_new_pack_id', params, function( response ) {
                        // get_new_pack_id
                        //alert( response );
                        var decodedResponse = jQuery.parseJSON( response );

                        thisContext.editing = false;
                        thisContext.post_id = decodedResponse.post_id;
                        thisContext.created = decodedResponse.created;
                        thisContext.pack_num = decodedResponse.pack_no;
                        thisContext.pack_type = 'stock';

                        thisContext.process_hide_event = true;

                        if ( !thisContext.modal ) { 
                            thisContext.modal = new bootstrap.Modal( document.getElementById( 'packing_list_dialog' ), { keyboard: true, backdrop: 'static' } );
                            var myModalEl = document.getElementById( 'packing_list_dialog' )
                            myModalEl.addEventListener('hide.bs.modal', (event) => {
                              //  alert( 'hiding' );
                                thisContext.handleHideEvent();
                            });
                        }
                       
                        thisContext.add_edit_pack_data = [];
                        thisContext.modal.show();
                        thisContext.addNewLine();
                    });

                });

                this.updateData();
                this.$forceUpdate();
            }
        });
    }
}

jQuery( document ).ready( function() { 
        gsSetupPackingList(); 
        gsSetupShippingList();
    } 
);