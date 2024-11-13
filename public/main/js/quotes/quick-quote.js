    // ***********************************
    // QUICK QUOTE BOX
    // ***********************************

    if ( jQuery( '#quick_quote_modal' ).length ) {
        var baseapp = new Vue({
        el: '#quick_quote_modal',
        data: {
            basecost: 2700,
            basecostsize: '44',
            ripskufactor: '44RIP1',
            yield_cross: 0.9,
            yield_rip: 0.8,
            yield_moulder: 0.95,
            labor_mult: GSM.config.labor_multiplier,
            mill_markup: GSM.qc_mill_markup,
            width: 0.5,
            length: 1000,
            setup_charge: GSM.config.custom_setup_charge,
            product_markup: 1.3,
            edge_glue: 0,
            knife_charge: 1,
            knife_charge_value: GSM.config.knife_charge,
            finish: 'unfinished',
            gsl_cost: 0,
            gsm_cost: 0,
            gsl_total_cost: 0,
            thickness: 0.25,
            species: 'poplar',

            setup_charge_enabled: 1,
            old_setup_charge_value: GSM.config.custom_setup_charge,
            old_knife_charge_value: 0,

            // sku selector
            sku: 'CUSTOM',
            sku_post_id: 0,
            previous_sku: '',
            previous_sku_post_id: 0,
            sku_changed: true
        },
        methods: {
            updateKnifeCost() {
                if ( this.old_knife_charge_value > 0 ) {
                    this.knife_charge_value = this.old_knife_charge_value;

                    this.old_knife_charge_value = 0;
                } else {
                    this.knife_charge_value = GSM.config.knife_charge * Math.ceil( this.width );
                }  
            },
            updateKniftCostEnable() {    
                if ( this.knife_charge == 1 ) {
                    this.updateKnifeCost();
                } else {
                    this.old_knife_charge_value = this.knife_charge_value;

                    this.knife_charge_value = 0;
                }

                this.updateCosts();
            },
            handleSetupChargeChange() {
                this.old_setup_charge_value = this.setup_charge;

                this.updateCosts();
            },
            updateCosts() {      
                var factor = parseFloat( jQuery( '#qc_ripsku option[value=\'' + this.ripskufactor + '\']' ).attr( 'data-factor' ) )/1000.0;

            // alert( this.labor_mult + ' ' + this.mill_markup + ' ' + this.yield_cross  +  ' ' +  this.yield_rip + ' ' + this.yield_moulder );
                var baseWoodCost = ( parseFloat( this.basecost ) * this.labor_mult * this.mill_markup ) / ( parseFloat( this.yield_cross ) * this.yield_rip * this.yield_moulder );

                var cost = parseFloat( baseWoodCost * factor );
                var baseCost = cost * parseFloat( this.length );
        
                var knifeCost = 0;
                var totalCost = 0;
                var edgeCost = 0;
                var finishCost = 0;

                // Fix setup charge so added when this is less than 1000 pre finishing costs
                totalCost = baseCost;
                if ( totalCost < GSM.config.setup_charge_threshold ) {
                    if ( this.setup_charge == 0 ) {
                        this.setup_charge = this.old_setup_charge_value;
                    }

                    this.setup_charge_enabled = 1;

                    totalCost = totalCost + parseFloat( this.setup_charge );
                } else {
                    this.setup_charge_enabled = 0;
                    this.setup_charge = 0;
                }

                if ( this.edge_glue == 1 ) {
                    // add edge glue charge
                    edgeCost = 0.07 * baseCost;
                }

                if ( this.knife_charge == 1 ) {
                    knifeCost = parseFloat( this.knife_charge_value );
                }

                if ( this.finish == 'primed' ) {
                // finishCost = parseFloat( this.length ) * GSM.config.primed_cost;
                    finishCost = parseFloat( this.length ) * gsCalculatePrimingCost( this.width );
                } else if ( this.finish == 'clear-coat' ) {
                    finishCost = parseFloat( this.length ) * GSM.config.clearcoat_cost;
                }
            
                totalCost = totalCost + knifeCost + edgeCost + finishCost;

                this.gsm_cost = ( parseFloat( totalCost ) / parseFloat( this.length ) ).toFixed( 2 );

                var totalCostPlusMarkup = ( parseFloat( totalCost ) * parseFloat( this.product_markup ) ) / parseFloat ( this.length );
                this.gsl_cost = totalCostPlusMarkup.toFixed( 2 );
                this.gsl_total_cost = ( parseFloat( this.gsl_cost ) * this.length ).toFixed( 2 );

            },
            updateRipSku() {
                var params = {
                    thickness: parseFloat( this.thickness ),
                    width: parseFloat( this.width ),
                }

                var context = this;
    
                gsmAdminAjax( 'quick_quote_ripsku', params, function( response ) {
                    var decoded = jQuery.parseJSON( response );
                    context.ripskufactor = decoded.rip_sku;

                    context.updateCosts();
                });
            },
            handleWidthChange() {
                this.updateKnifeCost();
                this.updateRipSku();
            },
            handleThicknessChange() {
                this.updateRipSku();
            },
            updateYields() {
                var selectedItem = jQuery( '#qc_species option[value="' + this.species + '"]');
                this.yield_cross = selectedItem.attr( 'data-cross_cut' );
                this.yield_rip = selectedItem.attr( 'data-rip' );;
                this.yield_moulder = selectedItem.attr( 'data-moulder' );
            },
            handleSpeciesChange() {
                this.updateYields();
                this.updateCosts();
            },
            formatNumber( num ) {
                return parseFloat( num ).toFixed (2 );
            },
            onSkuChange() {
            },
            onSkuFocus() {
                this.previous_sku = this.sku;
                this.previous_sku_post_id = this.sku_post_id;

                this.sku = '';
                this.showSkuDropdown();

                this.sku_changed = false;
            },
            showSkuDropdown() {
                
            },
            hideSkuDropdown() {
                jQuery( '.input-group.qc-sku-selector' ).hide();
            },
            onSkuKeydown( e ) {

            },
            getSkuInfo() {
                this.sku_changed = true;
                if ( this.sku_post_id > 0 ) {
                    var params = {
                        post_id: this.sku_post_id
                    }

                    var vueObject = this;

                    gsmAdminAjax( 'quick_quote_sku', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );

                        vueObject.ripskufactor = decodedResponse.ripsku;
                        vueObject.thickness = decodedResponse.thickness;
                        vueObject.width = decodedResponse.width;
                        
                        vueObject.knife_charge = 0;
                        vueObject.updateKnifeCost();   
                        vueObject.updateKniftCostEnable();  

                        vueObject.updateCosts();
                    });
                } else {
                    this.ripskufactor = '44RIP1';
                    this.thickness = 0.25;
                    this.width = 0.5;

                    if ( this.sku == 'CUSTOM' ) {
                        // custom
                        this.knife_charge = 1;
                    } else if ( this.sku == 'S4S' ) {
                        // s4s
                        this.knife_charge = 0;
                    } 

                    this.updateKnifeCost();   
                    this.updateKniftCostEnable();  
                    this.updateCosts();
                }
            },
            onSkuKeyup( e ) {
                var inputBox = jQuery( this );
                this.sku = this.sku.toUpperCase();

                if ( this.sku.length >= 2 ) {
                    jQuery( '.input-group.qc-sku-selector' ).show();
                }

                var vueObject = this;

                jQuery( ".input-group.qc-sku-selector li" ).each( function() {
                    if ( jQuery( this ).text().search( vueObject.sku ) > -1 ) {
                        jQuery( this ).show();
                    } else {
                        jQuery( this ).hide();
                    }
                });

                if ( e.keyCode == 13 ) {
                    var allVisible = jQuery( ".input-group.qc-sku-selector li:visible" );
                    if ( allVisible.length == 1 ) {
                        e.preventDefault();

                        vueObject.sku = allVisible.html();
                        vueObject.sku_post_id = allVisible.attr( 'data-post-id' );

                        vueObject.getSkuInfo();
                        vueObject.hideSkuDropdown();
                        
                        jQuery( '#quick_quote_modal' ).focus();
                    }
                }
            }
        },
        beforeMount() {
            this.updateKnifeCost();
            this.updateYields();
            this.updateCosts();
        },
        mounted() {
            var vueObject = this;

            jQuery( '.input-group.qc-sku-selector li' ).click( function( e ) {
                e.preventDefault;
                var postID = jQuery( this ).attr( 'data-post-id' );
                var name = jQuery( this ).html();

                vueObject.sku = name;
                vueObject.sku_post_id = postID;

                vueObject.getSkuInfo();

                vueObject.hideSkuDropdown();
            });
        }
    });
}

jQuery( 'a.quick-quote' ).click( function( e ) {
    var myModal = new bootstrap.Modal( document.getElementById( 'quick_quote_modal' ) );
    myModal.show();

  //  jQuery( '#quick_quote_modal' ).modal( {} );
    e.preventDefault();
});