 // ***********************************
    // LINE ITEM FUNCTIONS
    // ***********************************
    // Create a javascript object to hold the configuration called GSM
    // ***********************************

    function gsEnableConfigureButton( element ) {
    element.find( 'button.options' ).removeAttr( 'disabled' );
}

function gsEnableOverrideButton( element ) {
    element.find( 'button.overrides' ).removeAttr( 'disabled' );
}

function gsClearFields( element ) {
    element.removeAttr( 'data-species' );
    element.removeAttr( 'data-finish' );
    element.removeAttr( 'data-notes' );
    element.removeAttr( 'data-edge-glue' );
    element.removeAttr( 'data-knife-charge' );
    element.removeAttr( 'data-custom-thickness' );
    element.removeAttr( 'data-custom-width' );
    element.removeAttr( 'data-custom-thickness-friendly' );
    element.removeAttr( 'data-custom-width-friendly' );
    element.removeAttr( 'data-custom-ripsku' );
    element.removeAttr( 'data-custom-desc' );
    element.removeAttr( 'data-custom-sku' );
    element.removeAttr( 'data-drawing-url' );
    element.removeAttr( 'data-drawing-name' );
   // element.removeAttr( 'data-setup-charge-type' );

   //  element.attr( 'data-markup', GSM.default_markup );
    element.attr( 'data-pieces', 1 );
    element.attr( 'data-markup', 'default' );
    element.attr( 'data-finish', 'unfinished' );
    element.attr( 'data-edge-glue', 0 );
    element.attr( 'data-knife-charge', 0 );
    element.attr( 'data-custom-behavior', 'default' );
    element.attr( 'data-gsm-adjust', '1.00' );
    element.attr( 'data-include-setup-charge', '1' );
 //   element.attr( 'data-unit-measure', 'lf' );

    element.attr( 'data-custom-thickness', GSM.custom_thickness );
    element.attr( 'data-custom-width', GSM.custom_width );
    
    element.find( '.wood, .notes' ).html( '' );
}

function gsUpdateAllInfo( element, info ) {
    element.attr( 'data-width', info.friendly_width );
    element.attr( 'data-real-width', info.width );
    element.attr( 'data-thickness', info.friendly_thickness );
    element.attr( 'data-ripsku', info.ripsku );
    element.attr( 'data-profile', info.profile );
   
    if (typeof info.normal_or_s4s !== 'undefined') {
        element.attr( 'data-setup-charge-type', info.normal_or_s4s );
    } else {
        element.removeAttr( 'data-setup-charge-type' );
    }
    
    if ( info.mill_drawing ) {
        element.attr( 'data-mill-drawing', info.mill_drawing.url )
    } else  {
        element.removeAttr( 'data-mill-drawing' );
    }

    var allSpecies = '';

    jQuery.each( info.pricing, function( index, value ) {
        var niceString = gsMakeNiceString( index );
        element.attr( 'data-species-price-' + niceString, value );
        allSpecies = allSpecies + ' ' + niceString;
    } );

    element.attr( 'data-all-species', allSpecies.trim() );

    gsClearFields( element );    
    gsEnableConfigureButton( element );
    gsUpdateRowInformation( element );
}

function gsUpdateAllRows() {
    jQuery( '#cost_table tbody tr' ).each( function() {
        var thisRow = jQuery( this );
        if ( !thisRow.hasClass( 'dummy' ) ) {
            gsUpdateRowInformation( thisRow );
        }
    });
}

function gsFixWidth( width ) {
    return width.replace( ' ', '-'  );
}

function gsUpdateRowInformation( row ) {
    var sku = row.find( '.sku-selector' ).attr( 'data-sku' );
    
    var friendlySpecies = false;
    var selectedSpecies = row.attr( 'data-species' );
// Check selection of species
    if ( selectedSpecies == 'custom' ) {
        friendlySpecies = row.attr( 'data-species-custom' );
    } else {
        var species = jQuery( '#species option[value="' + selectedSpecies + '"]' );
        if ( species.length ) {
            friendlySpecies = species.html();
        }
    }

    var friendlyFinish = '';
    var rowFinish = row.attr( 'data-finish' );
    
    if ( rowFinish !== undefined ) {
        friendlyFinish = jQuery( 'option.' + row.attr( 'data-finish' ) ).html();
    }
    
    var lineNotes = row.attr( 'data-notes' );
    var lineMarkup = row.attr( 'data-markup' );
    if ( sku == 'none' || sku.length == 0 ) {
        row.find( 'div.desc' ).html();
    } else if ( sku == 'custom' || sku == 's4s' ) {
        var sizeString = '';

        sizeString =  jQuery( '#custom_thickness option[value="' + row.attr( 'data-custom-thickness' ) + '"]' ).html() + ' x ' +  jQuery( '#custom_width option[value="' + row.attr( 'data-custom-width' ) + '"]' ).html();

        var customProfileDesc = row.attr( 'data-custom-desc' );
        if ( customProfileDesc.length ) {
            sizeString = sizeString + ' ' + customProfileDesc;
        }

        var customProfileSku = row.attr( 'data-custom-sku' );
        if ( customProfileSku.length ) {
            sizeString = 'SKU: ' + customProfileSku + "<br/>" + sizeString; 
        }

        var millDrawing = row.attr( 'data-drawing-url' );
        if ( millDrawing ) {
            row.find( 'a.mill-drawing' ).attr( 'href', millDrawing ).show();
        } else {
            row.find( 'a.mill-drawing' ).hide();
        }

        row.find( 'div.desc' ).html( sizeString );
    } else {
        // Normal profiles
        var sizeString = gsFixWidth( row.attr( 'data-thickness' ) ) + '" x ' + gsFixWidth( row.attr( 'data-width' ) ) + '" ' + row.attr( 'data-profile' );

        var millDrawing = row.attr( 'data-mill-drawing' );
        if ( millDrawing ) {
            row.find( 'a.mill-drawing' ).attr( 'href', millDrawing ).show();
        } else {
            row.find( 'a.mill-drawing' ).hide();
        }

        row.find( 'div.desc' ).html( sizeString );
    }

    if ( friendlySpecies ) {
        jQuery( row.find( '.wood' ) ).html( '<strong>' + friendlySpecies.toUpperCase() + '</strong>' );

        if ( row.attr( 'data-edge-glue' ) == 1 ) {
            friendlyFinish = friendlyFinish + ' EDGE GLUED';
        }
    }

    jQuery( row.find( '.finish' ) ).html( '<span>' + friendlyFinish.toUpperCase() + '</span>' );
  
    if ( lineNotes !== undefined && lineNotes.length ) {
        var notesString = 'Notes: ' + lineNotes;
        row.find( '.notes' ).html( notesString );
    } else {
        row.find( '.notes' ).html( '' );
    }
// Print the width and thickness in the console
    gsmLogMessage( 'Width: ' + row.attr( 'data-width' ) + ', Thickness: ' + row.attr( 'data-thickness' ) );

// Print the finish in the console
    gsmLogMessage( 'Finish: ' + row.attr( 'data-finish' ) );

// Print the profile in the console
gsmLogMessage( 'Profile: ' + row.attr( 'data-profile' ) );
}

function gsAddRowToTable() {
    var dummyData = jQuery( 'tr.dummy' ).html();
    jQuery( '#cost_table tbody' ).append( '<tr data-markup="' + GSM.default_markup + '" class="new-item" data-unit-measure="lf" data-gsm-adjust="1.00" data-is-active="1">' + dummyData + '</tr>' );
    jQuery( 'tr.new-item' ).find( 'btn.overrides' ).prop( 'disabled', true ).removeClass( 'new-item' );

    gsRenumberRows();
}

function gsRenumberRows() {
    //alert( 'here' );
    var index = 0;
    jQuery( 'tbody tr' ).each( function( e ) {
        jQuery( this ).attr( 'data-num', index );
        index = index + 1;
    });

    // Let's look at mill drawings here too - a bit clunky but need to fix it
    jQuery( 'tbody tr' ).each( function( e ) {
        if ( jQuery( this ).hasClass( 'is-custom' ) ) {
            if ( !jQuery( this ).attr( 'data-drawing-url' ) ) {
                jQuery( this ).find( 'a.mill-drawing' ).hide();
            }
        } else {
            if ( !jQuery( this ).attr( 'data-mill-drawing' ) ) {
                jQuery( this ).find( 'a.mill-drawing' ).hide();
            }
        }
    });
}

function gsmIsTaxable() {
    var taxAmount = jQuery( '#tax_rate' ).val();
    return( taxAmount > 0.0001 );
}

function gsmGetTaxRate() {
    var taxValue = jQuery( '#tax_rate' ).val();
    return parseFloat( taxValue );  
}

function gsPriceTableUpdateLf() {
    jQuery( '#cost_table tbody tr' ).each( function( index, e ) {
        if ( !jQuery( this ).hasClass( 'dummy' ) ) {
            var quantity = parseFloat( jQuery( this ).find( '.qty' ).val() );
            var length = parseFloat( jQuery( this ).attr( 'data-pc-length' ) );
            var totalPrice = jQuery( this ).attr( 'data-calculated-price-markup' );

            var priceUnitMeasure = jQuery( this ).attr( 'data-unit-measure' );
            if ( quantity > 0 && totalPrice >= 0 && !isNaN( totalPrice ) && jQuery( this ).attr( 'data-is-active' ) == 1 ) {     
                if ( priceUnitMeasure == 'pc' ) {
                    // price is per piece
                    jQuery( this ).find( '.um div' ).html( priceUnitMeasure.toUpperCase() + '<br/>' + length + '\'' );
                } else {
                    jQuery( this ).find( '.um div' ).html( priceUnitMeasure.toUpperCase() );
                }

                var pricePer = totalPrice / quantity;
                jQuery( this ).find( '.price-per-lf' ).html( '$' + pricePer.toFixed( 2 ) );
              
            } else {
                jQuery( this ).find( '.price' ).html( '$0.00' );
                jQuery( this ).find( '.price-per-lf' ).html( '$0.00' );

                jQuery( this ).removeAttr( 'data-calculated-price' );

                jQuery( this ).find( '.um div' ).html( priceUnitMeasure.toUpperCase() );
            }
        }
    });
}

function gsGetGlobalMarkup() {
    var globalMarkup = jQuery( '#master_markup' ).val();

    return globalMarkup;
}

function gsGetPriceForLineItemCustomS4S( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior ) {  
    var customRipSku = lineItem.attr( 'data-custom-ripsku' );
    var normalRipSku = lineItem.attr( 'data-ripsku' );
    var includeSetupCost = ( lineItem.attr( 'data-include-setup-charge' ) == '1' );

    if ( customRipSku == '' && ( sku != 'custom' && sku != 's4s' ) ) {
        customRipSku = normalRipSku;
    }

    if ( customRipSku ) {  
       // var customRipSku = lineItem.attr( 'data-custom-ripsku' );
        var ripFactor = jQuery( '#ripsku option[value="' + customRipSku + '"]' ).attr( 'data-factor' );
        var ripThick = jQuery( '#ripsku option[value="' + customRipSku + '"]' ).attr( 'data-thickness' );

        var ripBaseCost = 0;   
        if ( lineItem.attr( 'data-custom-thickness-price-' + ripThick ) ) {
            ripBaseCost = lineItem.attr( 'data-custom-thickness-price-' + ripThick );  
        } 

        var baseCost = parseFloat( ( ripBaseCost * ripFactor * quantity * piece_length / 1000.0 ).toFixed( 5 ) );
        var totalPieces = quantity / piece_length;

        gsmLogMessage( 'gsGetPriceForLineItemCustomS4S, ' + ripFactor + ' ' + ripThick + ' ' + ripBaseCost + ' ' + baseCost + ' ' + totalPieces );

        var customWidth = lineItem.attr( 'data-custom-width' );

        var tempBehaviour = overrideBehavior;
        if ( tempBehaviour == 'override-final' ) {
            tempBehaviour = 'default';
        }

        switch( tempBehaviour ) {
            case 'default':
                returnObj.totalPrice = parseFloat( baseCost );

                // Setup cost
                var setupCost = 0;
                if ( sku == 'custom'  ) {
                    if ( baseCost < GSM.config.setup_charge_threshold ) {
                        setupCost = GSM.config.custom_setup_charge;
                    }
                } else if ( $sku = 's4s' ) {
                    if ( baseCost < GSM.config.setup_charge_threshold ) {
                        setupCost = GSM.config.s4s_setup_charge;
                    } 
                }

                if ( !includeSetupCost ) {
                    setupCost = 0;
                }

                returnObj.totalPrice = returnObj.totalPrice + setupCost;

                // Edge Glue Cost
                var edgeGlueCost = 0;
                var hasEdgeGlue = lineItem.attr( 'data-edge-glue' );
                if ( hasEdgeGlue == '1' ) {
                    edgeGlueCost = parseFloat( parseFloat( baseCost * GSM.config.edge_glue_factor ).toFixed( 2 ) );
                }
                returnObj.totalPrice = returnObj.totalPrice + edgeGlueCost;

                // Finish
                var finishCost = 0;
                var finishType = lineItem.attr( 'data-finish' );
                if ( finishType == 'primed' ) {
                    if ( typeof GSM.config.primed_matrix != undefined ) {
                        finishCost = quantity * piece_length * gsCalculatePrimingCost( customWidth );
                       // alert( gsCalculatePrimingCost( customWidth ) ); 
                    } else {
                        finishCost = quantity * piece_length * GSM.config.primed_cost;
                    }
                } else if ( finishType == 'clear-coat' ) {
                    finishCost = quantity * piece_length * GSM.config.clearcoat_cost;
                }

                returnObj.totalPrice = returnObj.totalPrice + finishCost;

                // Knife cost
                var knifeCost = 0;
                var hasKnife = lineItem.attr( 'data-knife-charge' );
                if ( hasKnife == '1' ) {
                    knifeCost = Math.ceil( lineItem.attr( 'data-custom-width' ) ) * GSM.config.knife_charge;
                } 

                returnObj.totalPrice = returnObj.totalPrice + knifeCost;
                // Add GSM Adjust
                //var gsmAdjust = parseFloat( lineItem.attr( 'data-gsm-adjust' ) );

                var gsmAdjust = 1.00;
                if ( lineItem.attr( 'data-gsm-adjust' ) != '1.00' ) {
                    gsmAdjust = lineItem.attr( 'data-gsm-adjust' );
                }

                returnObj.totalPrice = returnObj.totalPrice * parseFloat( gsmAdjust );

                returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + returnObj.totalPrice;

                gsmLogMessage( '...CUSTOM: baseCost ' + baseCost + ',setupCost ' + setupCost  + ',edgeGlueCost ' + edgeGlueCost + ',finishCost: ' + finishCost + ',knifeCost ' + knifeCost + ", total: " + returnObj.totalPrice );

                break;

            case 'override-individual':
                var x = parseFloat( lineItem.attr( 'data-override-setup' ) );

                var setupCharge = parseFloat( lineItem.attr( 'data-override-setup' ) );

                if ( !includeSetupCost ) {
                    setupCharge = 0;
                }

                var knifeCharge = 0;
                var hasKnife = lineItem.attr( 'data-knife-charge' );
                if ( hasKnife == '1' ) {
                    knifeCharge = parseFloat( lineItem.attr( 'data-override-knife' )  );
                }

                var edgeCharge = 0;
                if ( lineItem.attr( 'data-override-edge' ) ) {
                    edgeCharge = parseFloat( lineItem.attr( 'data-override-edge' )  );
                }

                var finishCharge = parseFloat( lineItem.attr( 'data-override-finish' )  );

                returnObj.totalPrice = baseCost + ( edgeCharge + finishCharge ) * quantity * piece_length + ( knifeCharge + setupCharge );

                // Add GSM Adjust
                var gsmAdjust = 1.0;
                if ( lineItem.attr( 'data-gsm-adjust' ) != '1.00' ) {
                    gsmAdjust = lineItem.attr( 'data-gsm-adjust' );
                }

                
                returnObj.totalPrice = returnObj.totalPrice * parseFloat( gsmAdjust );

                returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + returnObj.totalPrice;

                gsmLogMessage( '...CUSTOM-OVERRIDE-INDIV: baseCost ' + baseCost + ',setupCost ' + setupCharge  + ',edgeGlueCost ' + edgeCharge + ',finishCost: ' + finishCharge + ',knifeCost ' + knifeCharge + ", total: " + returnObj.totalPrice );
                break;

            case 'override-final':
              // alert( 'here' );
              //  die;
                break;
        }

        if ( overrideBehavior == 'override-final' ) {
            var totalCharge = lineItem.attr( 'data-override-charge' ) 
            returnObj.totalPrice = totalCharge * quantity;

            gsmLogMessage( '...CUSTOM-OVERRIDE-FINAL: baseCost ' + baseCost + ',setupCost ' + setupCost  + ',edgeGlueCost ' + edgeGlueCost + ',finishCost: ' + finishCost + ',knifeCost ' + knifeCost + ", total: " + returnObj.totalPrice );
        }
    }

    return returnObj;
}

function gsmFixPriceQuantityLF( price, quantity ) {
    var pricePerLf = round( ( price / quantity ) * 100 );

    return ( pricePerLf / 100.0 ) * quantity;
}

function gsGetPriceForLineItemCustomSpecies( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior, selectedSpecies, selectedFinish ) {
    var totalCharge = lineItem.attr( 'data-override-charge' );
    returnObj.totalPrice = totalCharge * quantity;

    var totalGSMCharge = lineItem.attr( 'data-override-gsm' );
    if ( totalGSMCharge > 0 ) {
        returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + totalGSMCharge * quantity;
    } else {
        returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + returnObj.totalPrice;
    }
    
    gsmLogMessage( "gsGetPriceForLineItemNormalSKUCustomSpecies: total: " + returnObj.totalPrice, ' totalMarkup: ' + returnObj.totalPriceForMarkup );

    return returnObj;
}

function gsGetPriceForLineItemNormalSKU( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior, selectedSpecies, selectedFinish ) {
    var pricing = parseFloat( lineItem.attr( 'data-species-price-' + selectedSpecies ) );
    var includeSetupCost = ( lineItem.attr( 'data-include-setup-charge' ) == '1' );

    gsmLogMessage( "gsGetPriceForLineItemNormalSKU: pricing " + pricing );

    if ( pricing ) {
        var tempBehaviour = overrideBehavior;
        if ( tempBehaviour == 'override-final' ) {
            tempBehaviour = 'default';
        }

        var realWidth = lineItem.attr( 'data-real-width' );

        pricing = gsTruncateNumber( pricing, 5 );

        switch ( tempBehaviour ) {
            case 'default':
                returnObj.totalPrice = pricing * quantity * piece_length;

                var setupCost = 0;
                if ( returnObj.totalPrice < GSM.config.setup_charge_threshold ) {
                    var setupChargeType = lineItem.attr( 'data-setup-charge-type' );
                    if ( setupChargeType == 's4s' ) {
                        setupCost = GSM.config.s4s_setup_charge;  
                    } else {
                        setupCost = GSM.config.custom_setup_charge;  
                    }
                }

                if ( !includeSetupCost ) {
                    setupCost = 0;
                }

                returnObj.totalPrice = returnObj.totalPrice + setupCost;

                var finishCost = 0;
                if ( selectedFinish == 'primed' ) {
                    if ( typeof GSM.config.primed_matrix != undefined ) {
                
                        finishCost = gsCalculatePrimingCost( realWidth );
                    } else {
                        finishCost = GSM.config.primed_cost;
                    }
                } else if ( selectedFinish == 'clear-coat' ) {
                    finishCost = GSM.config.clearcoat_cost;
                }

                finishCost = gsTruncateNumber( finishCost, 2 );

                returnObj.totalPrice = returnObj.totalPrice + quantity * piece_length * finishCost;

                // Edge Glue Cost
                var edgeGlueCost = 0;
                var hasEdgeGlue = lineItem.attr( 'data-edge-glue' );

                if ( hasEdgeGlue == '1' ) {
                    edgeGlueCost = pricing * GSM.config.edge_glue_factor * quantity * piece_length;

                    edgeGlueCost = gsTruncateNumber( edgeGlueCost, 5 );
                    returnObj.totalPrice = returnObj.totalPrice + parseFloat( edgeGlueCost );
                }

                // Add GSM
                var gsmAdjust = 1.0;
                if ( lineItem.attr( 'data-gsm-adjust' ) != '1.00' ) {
                    gsmAdjust = lineItem.attr( 'data-gsm-adjust' );
                }
                returnObj.totalPrice = returnObj.totalPrice * parseFloat( gsmAdjust );

                returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + returnObj.totalPrice;

                gsmLogMessage( '...NORMAL: baseCost ' + pricing + ", edgeGlueCost: " + edgeGlueCost + ", finishCost: " + finishCost + ", setupCost: " + setupCost + ", total: " + returnObj.totalPrice );

                break;
            case 'override-individual':
                var setupCharge = parseFloat( lineItem.attr( 'data-override-setup' ) );

                 if ( !includeSetupCost ) {
                    setupCharge = 0;
                }

                var finishCharge = parseFloat( lineItem.attr( 'data-override-finish' ) );

                var edgeCharge = 0;
                if ( lineItem.attr( 'data-override-edge' ) ) {
                    edgeCharge = parseFloat( lineItem.attr( 'data-override-edge' )  );
                }

                /*
                alert( pricing );
                alert( finishCharge );
                alert( setupCharge );
                */


                returnObj.totalPrice = ( pricing + finishCharge + edgeCharge ) * quantity * piece_length + setupCharge;

                // Add GSM Adjust
                var gsmAdjust = 1.0;
                if ( lineItem.attr( 'data-gsm-adjust' ) != '1.00' ) {
                    gsmAdjust = lineItem.attr( 'data-gsm-adjust' );
                }
                returnObj.totalPrice = returnObj.totalPrice * parseFloat( gsmAdjust );

                returnObj.totalPriceForMarkup = returnObj.totalPriceForMarkup + returnObj.totalPrice;

                break;    
            case 'override-final':
                die;
                
                break;
        }

        // Update the final price
        if ( overrideBehavior == 'override-final' ) {
            var totalCharge = parseFloat( lineItem.attr( 'data-override-charge' ) ); 
            returnObj.totalPrice = totalCharge * quantity;
            
            gsmLogMessage( '...CUSTOM-OVERRIDE-FINAL: Total: ' + returnObj.totalPrice );
        }
    }

    return returnObj;
}

function gsGetPriceForLineItem( lineItem ) {
    var sku =  lineItem.find( '.sku-selector' ).attr( 'data-sku' );
    var quantity = parseFloat( lineItem.find( '.qty' ).val() );
    var unit_measure = lineItem.attr( 'data-unit-measure' );
    var piece_length = 1;

   // Print the sku, quantity and unit measure in the console
    gsmLogMessage( 'SKU: ' + sku + ', Quantity: ' + quantity + ', Unit Measure: ' + unit_measure );
  // Print the ripsku in the console
    gsmLogMessage( 'Rip SKU: ' + lineItem.attr( 'data-ripsku' ) );
    if ( unit_measure == 'pc' ) {
        // per piece
        piece_length = lineItem.attr( 'data-pc-length' );
    }

    var selectedSpecies = lineItem.attr( 'data-species' );
   // Print the selected species in the console
    gsmLogMessage( 'Selected Species: ' + selectedSpecies );

    var selectedFinish = lineItem.attr( 'data-finish' );
    var overrideBehavior = lineItem.attr( 'data-custom-behavior' );

    var returnObj = {
        priceSet: false,
        totalPrice: 0,
        totalPriceForMarkup: 0
    }

    if ( selectedSpecies == 'custom' ) {
        returnObj = gsGetPriceForLineItemCustomSpecies( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior, selectedSpecies, selectedFinish );
    } else {
        if ( sku == 'custom' || sku == 's4s' ) {
            returnObj = gsGetPriceForLineItemCustomS4S( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior );
        } else {
            returnObj = gsGetPriceForLineItemNormalSKU( returnObj, lineItem, sku, quantity, piece_length, overrideBehavior, selectedSpecies, selectedFinish );
        }   
    }

    return returnObj;
}

function gsShouldIncludeMarkup( lineItem ) {
    var sku =  lineItem.find( '.sku-selector' ).attr( 'data-sku' );
    var overrideBehavior = lineItem.attr( 'data-custom-behavior' );

    if ( overrideBehavior == 'override-final' ) {
        return false;
    } else {
        return true;
    }
}

function gsFixupPrice( quantity, price ) {
    var pricePerEach = price / quantity;
    price = pricePerEach.toFixed( 2 ) * quantity;

    return price;
}

function gsUpdateTablePricing() {
    var tableData = {
        totalPrice: 0,
        totalPriceForMarkupCalc: 0,
        totalMarkup: 0,
        totalPriceWithMarkup: 0
    }

    gsmLogMessage( '------- CALCULATING PRICING ------' );
   
    jQuery( '#cost_table tbody tr' ).removeClass( 'pending-markup' );

    // Calculate total prices
    jQuery( '#cost_table tbody tr' ).each( function( index, e ) {
        if ( !jQuery( this ).hasClass( 'dummy' ) ) {
            var lineItem = jQuery( this );

            // Don't include it in our price
            if ( lineItem.attr( 'data-is-active' ) == 0 ) {
                return;
            }

            var priceForThisLine = gsGetPriceForLineItem( lineItem );

          //  alert( priceForThisLine.totalPrice );

            // Update price information for line item
       //     alert( priceForThisLine.totalPrice  );
            lineItem.attr( 'data-calculated-price', priceForThisLine.totalPrice );

            tableData.totalPrice = tableData.totalPrice + priceForThisLine.totalPrice;
            tableData.totalPriceForMarkupCalc = tableData.totalPriceForMarkupCalc + priceForThisLine.totalPriceForMarkup;
        }   
    }); 

//    alert( tableData.totalPrice + ' ' + tableData.totalPriceWithMarkup );

    var globalMarkup = gsGetGlobalMarkup();
    if ( globalMarkup == 'auto' ) {
        if ( tableData.totalPriceForMarkupCalc > 20000 ) {
            globalMarkup = GSM.config.tiers.more_20000;
        } else if ( tableData.totalPriceForMarkupCalc > 15000 ) {
            globalMarkup = GSM.config.tiers.more_15000;
        } else if ( tableData.totalPriceForMarkupCalc > 10000 ) {
            globalMarkup = GSM.config.tiers.more_10000;
        } else if ( tableData.totalPriceForMarkupCalc > 7500 ) {
            globalMarkup = GSM.config.tiers.more_7500;
        } else if ( tableData.totalPriceForMarkupCalc > 5000 ) {
            globalMarkup = GSM.config.tiers.more_5000;
        } else if ( tableData.totalPriceForMarkupCalc > 3000 ) {
            globalMarkup = GSM.config.tiers.more_3000;
        } else if ( tableData.totalPriceForMarkupCalc > 1000 ) {
            globalMarkup = GSM.config.tiers.more_1000;
        } else if ( tableData.totalPriceForMarkupCalc > 500 ) {
            globalMarkup = GSM.config.tiers.more_500;
        } else {
            globalMarkup = GSM.config.tiers.less_equal_500;
        }
    } 
    
    globalMarkup = parseFloat( globalMarkup );

    // Calculate and markups
    jQuery( '#cost_table tbody tr' ).each( function( index, e ) {
        if ( !jQuery( this ).hasClass( 'dummy' ) ) {
            var lineItem = jQuery( this );

             // Don't include it in our price
            if ( lineItem.attr( 'data-is-active' ) == 0 ) {
                lineItem.find( '.price' ).html( gsNumberFormat.format( 0 ) );
                return;
            }

            if ( gsShouldIncludeMarkup( lineItem ) ) {
                var selectedMarkup = lineItem.attr( 'data-markup' );
    
                if ( selectedMarkup == 'default' ) {
                    var calculatedMarkup = globalMarkup;
                } else {
                    var calculatedMarkup = selectedMarkup;
                }
        
                lineItem.attr( 'data-calculated-markup-factor', calculatedMarkup );
        
                var calculatedPrice = lineItem.attr( 'data-calculated-price' );
                var markup = ( calculatedMarkup - 1.0 ) * calculatedPrice;
                var newPrice = calculatedPrice * calculatedMarkup;

                // going to filter price here
                var quantity = lineItem.find( '.qty' ).val();
                newPrice = gsFixupPrice( quantity, newPrice );

                tableData.totalMarkup = tableData.totalMarkup + markup;
                tableData.totalPriceWithMarkup = tableData.totalPriceWithMarkup + newPrice;
    
                lineItem.attr( 'data-calculated-price-markup', newPrice );
                lineItem.find( '.price' ).html( gsNumberFormat.format( newPrice ) );
            } else {
                var calculatedPrice = parseFloat( lineItem.attr( 'data-calculated-price' ) );

                // going to filter price here
                var quantity = lineItem.find( '.qty' ).val();
                calculatedPrice = gsFixupPrice( quantity, calculatedPrice );

                lineItem.attr( 'data-calculated-price-markup', calculatedPrice );
                lineItem.attr( 'data-calculated-markup-factor', 1.0 );

                lineItem.find( '.price' ).html( gsNumberFormat.format( calculatedPrice ) );

                tableData.totalPriceWithMarkup = tableData.totalPriceWithMarkup + calculatedPrice;
            }
        }
    });
    
    gsPriceTableUpdateLf();

    jQuery( 'tr.subtotal td .price' ).html( gsNumberFormat.format( tableData.totalPriceWithMarkup ) )

    var freightCost = parseInt( jQuery( '#freight_cost' ).val() );
    if ( freightCost > 0 ) {
        tableData.totalPriceWithMarkup = tableData.totalPriceWithMarkup + freightCost;
    } 

    jQuery( '.freight td .price' ).html( gsNumberFormat.format( freightCost ) );

    var taxRate = 0;
    var tax = 0;
    if ( gsmIsTaxable() ) {
        taxRate = gsmGetTaxRate();
        tax = taxRate * tableData.totalPriceWithMarkup/100.0;

        jQuery( '.tax td.text-right' ).html( '<strong>Tax (' + taxRate.toFixed( 1 ) + '%)</strong>' );
    } else {
        jQuery( '.tax td.text-right' ).html( '<strong>Tax</strong>' );
    }

    jQuery( '.tax td .price' ).html( gsNumberFormat.format( tax ) );

    var totalPriceForEverythingPlusTax = tableData.totalPriceWithMarkup + tax;

    jQuery( '.total td .price' ).html( gsNumberFormat.format( totalPriceForEverythingPlusTax ) );

    if ( tableData.totalPriceForMarkupCalc > 0 ) {
        jQuery( '#calculated_markup' ).val( globalMarkup.toFixed( 2 ) );
    } else {
        jQuery( '#calculated_markup' ).val( parseFloat( GSM.config.tiers.less_equal_500 ).toFixed( 2 ) );
    }
}

function doGsSaveQuote( button, callback = 0, dim = 1,  ) {
    var quoteName = jQuery( '#contact_name' ).val();
    var customerName = jQuery( '#customer_name' ).val();
    var quotePhone = jQuery( '#phone' ).val(); 
    var quoteEmail = jQuery( '#email' ).val(); 
    var quoteCity = jQuery( '#city' ).val(); 
    var customerNotes = jQuery( '#customer_notes' ).val(); 
    var millNotes = jQuery( '#mill_notes' ).val(); 
    var gslpPo = jQuery( '#gslp_po' ).val();
    var customerPo = jQuery( '#customer_po' ).val();
    var leadTime = jQuery( '#lead_time' ).val();
    var freightCost = jQuery( '#freight_cost' ).val();
    var tax_rate = jQuery( '#tax_rate' ).val();
    var master_markup = jQuery( '#master_markup' ).val();
    var shipsFrom = jQuery( '#gslp_location' ).val();
    var isOrder = jQuery( '#gsm_is_order' ).val();
    var ltlPackaging = jQuery( '#ltl_packaging' ).val();
    var salesPerson = jQuery( '#quotation_by' ).val();
    var orderStatus = jQuery( '#order_status' ).val();

    var customerNameShip = '';
    var quoteNameShip = '';
    var quoteCityShip = ''; 
    var quotePhoneShip = ''; 

    var quoteAlternateShipAddress = jQuery( '#ship_address_same' ).is( ":checked" ) ? '1' : '0';
    if ( quoteAlternateShipAddress == '1' ) {
        customerNameShip = jQuery( '#contact_name_shipment' ).val();
        quoteNameShip = jQuery( "#customer_name_shipment" ).val();
        quoteCityShip = jQuery( '#city_shipment' ).val(); 
        quotePhoneShip = jQuery( '#phone_shipment' ).val(); 
    }

    // find all items
    var allItems = [];
    jQuery( '#cost_table tbody tr' ).each( function() {
        if ( !jQuery( this ).hasClass( 'dummy' ) && ( jQuery( this ).attr( 'data-species' ) || jQuery( this ).hasClass( 'is-freeform' ) || jQuery( this ).hasClass( 'is-custom' ) ) ) {
           // alert( jQuery( this ).find( 'select.sku-selector option:selected' ).text().toLowerCase() );

           //alert( jQuery( this ).attr( 'data-override-gsm' ) );
       
            var thisItem = {
                markup: jQuery( this ).attr( 'data-markup' ),
                gsm_adjust: jQuery( this ).attr( 'data-gsm-adjust' ),
                finish: jQuery( this ).attr( 'data-finish' ),
                species: jQuery( this ).attr( 'data-species' ),
                species_custom: jQuery( this ).attr( 'data-species-custom' ),
                notes: jQuery( this ).attr( 'data-notes' ),
                custom_desc: jQuery( this ).attr( 'data-custom-desc' ),
                custom_sku: jQuery( this ).attr( 'data-custom-sku' ),
                quantity: jQuery( this ).find( 'td input.qty' ).val(),
                post_id: jQuery( this ).find( 'input.sku-selector' ).attr( 'data-sku' ),
                sku: jQuery( this ).find( 'input.sku-selector' ).val().toLowerCase(),
                custom_thickness: jQuery( this ).attr( 'data-custom-thickness' ),
                unit_of_measure: jQuery( this ).attr( 'data-unit-measure' ),
                unit_pc_length: jQuery( this ).attr( 'data-pc-length' ),
                custom_width: jQuery( this ).attr( 'data-custom-width' ),
                custom_thickness_friendly: jQuery( this ).attr( 'data-custom-thickness-friendly' ),
                custom_width_friendly: jQuery( this ).attr( 'data-custom-width-friendly' ),
                custom_ripsku: jQuery( this ).attr( 'data-custom-ripsku' ),
                custom_behavior: jQuery( this ).attr( 'data-custom-behavior' ),
                edge_glue: jQuery( this ).attr( 'data-edge-glue' ),
                knife_charge: jQuery( this ).attr( 'data-knife-charge' ),
                override_charge: jQuery( this ).attr( 'data-override-charge' ),
                override_gsm: jQuery( this ).attr( 'data-override-gsm' ),
                override_finish: jQuery( this ).attr( 'data-override-finish' ),
                override_edge: jQuery( this ).attr( 'data-override-edge' ),
                override_setup: jQuery( this ).attr( 'data-override-setup' ),
                override_knife: jQuery( this ).attr( 'data-override-knife' ),
                drawing_name: jQuery( this ).attr( 'data-drawing-name' ),
                drawing_url: jQuery( this ).attr( 'data-drawing-url' ),
                is_active: jQuery( this ).attr( 'data-is-active' ),
                include_setup_charge: jQuery( this ).attr( 'data-include-setup-charge' )
            }
    
            allItems.push( thisItem );
        }
    });

    var items = JSON.stringify( allItems );

    var params = {
        contact_name: quoteName,
        customer_name: customerName,
        customer_phone: quotePhone,
        customer_email: quoteEmail,
        customer_city: quoteCity,
        customer_name_shipment: quoteNameShip,
        contact_name_shipment: customerNameShip,
        customer_city_shipment: quoteCityShip,
        customer_phone_shipment: quotePhoneShip,
        ship_address_same: quoteAlternateShipAddress,       
        lead_time: leadTime,
        customer_notes: customerNotes,
        gslp_location: shipsFrom,
        mill_notes: millNotes,
        quote_id: GSM.quote_id,
        items: allItems,
        gslp_po: gslpPo,
        customer_po: customerPo,
        tax_rate: tax_rate,
        master_markup: master_markup,
        freight_cost: freightCost,
        is_order: isOrder,
        ltl_packaging: ltlPackaging,
        sales_person: salesPerson
    };

    if ( orderStatus.length ) {
        params.order_status =  orderStatus;
    }

    var encoded = JSON.stringify( params );

    var oldButton = button.html();
    if ( dim ) {
        button.html( 'Saving...' );
        jQuery( 'body' ).css( 'opacity', '0.5' );
    }

    //console.log( 'Actual save quote...' + encoded );
    gsmAdminAjax( 'save_quote', params, function( response ) {
      //  console.log( 'Done actual save quote...' );
        if ( dim ) {
            setTimeout( 
                function() {
                    var decodedResponse = jQuery.parseJSON( response );
                    if ( decodedResponse.redirect ) {
                        document.location.href = decodedResponse.redirect_url;
                    } else {
                        button.html( oldButton );
                        jQuery( 'body' ).css( 'opacity', '1.0' );
                    }  
                },
                50
            );
        }

        if ( callback ) {
            callback();
        }   
    });
}



function gsCheckMarkupShowHide() {
    var currentValue =  jQuery( '#master_markup' ).val();
    if ( currentValue == 'auto' ) {
        jQuery( '.markup-cal' ).show();
    } else {
        jQuery( '.markup-cal' ).hide();
    }
}

function gsGetCustomerName() {
    var customerName = jQuery( '#customer_name' ).val();
    return customerName;
}

function gsCheckForQuantityNotZero() {
    
}

function gsCheckCanSaveQuote() {
    var customerName = gsGetCustomerName();
    if ( customerName.length == 0 ) {
        alert( 'You must enter the Company Name before saving the quotation' );
        return false;
    } else {
        return true;
    }
}


function gsCheckSubtotalShowHide() {
    var showSubTotal = false;

    var freightCost = jQuery( '#freight_cost' ).val();
    if ( parseInt( freightCost ) > 0 || gsmIsTaxable() ) {
        showSubTotal = true;
    }

    if ( showSubTotal ) {
        jQuery( 'tr.subtotal' ).show();
    } else {
        jQuery( 'tr.subtotal' ).hide();
    }
}

function gsFrieghtShowHide() {
    var freightCost = jQuery( '#freight_cost' ).val();
    if ( parseInt( freightCost ) > 0 ) {
        jQuery( 'tr.freight' ).show();
    } else {
        jQuery( 'tr.freight' ).hide();
    }
}

function gsUpdateSkuInformation( selectedParent, sku ) {
    // Update defaults here
    if ( sku == 'custom' || sku == 's4s' ) {
        selectedParent.find( 'div.finish' ).html( '' );
        selectedParent.addClass( 'is-custom' );
        selectedParent.attr( 'data-all-species', GSM.all_species );
        selectedParent.find( '.overrides' ).prop( 'disabled', 1 );
        selectedParent.attr( 'data-markup', GSM.default_markup );
    }

    if ( sku == 'custom' ) {
        selectedParent.find( 'div.desc' ).html( 'Custom' );
        selectedParent.attr( 'data-knife-charge', 1 );
    } else if ( sku == 's4s' ) {
        selectedParent.find( 'div.desc' ).html( 'S4S' );
        selectedParent.attr( 'data-knife-charge', 0 );
    } else {

    }
}

function gsCheckOrderTabBar() {
    if ( jQuery( '#ship_address_same' ).is( ":checked" ) ) { 
        jQuery( 'a[data-page="shipping-info"]' ).show();
    } else {
        jQuery( 'a[data-page="shipping-info"]' ).hide();
        jQuery( 'a[data-page="billing-info"]' ).click();
    }
}

function doGsOrderTabs() {
    // handle tabs
    jQuery( '.gsm-tabs a' ).click( function ( e ) {
        var mainUl = jQuery( this ).parent().parent();
        mainUl.find( 'a' ).removeClass( 'active' );
        jQuery( this ).addClass( 'active' );
        var pageName = jQuery( this ).attr( 'data-page' );
        var groupName = mainUl.attr( 'id' );
        jQuery( 'section [data-group=' + groupName + ']' ).hide();
        jQuery( 'section [data-page=' + pageName + ']' ).show();
        e.preventDefault();
    });

    gsCheckOrderTabBar();

    jQuery( '#ship_address_same' ).click( function( e ) {
        gsCheckOrderTabBar();
    });
}

function doGSready() {
    let hamburger = document.getElementById( 'burger' );
    let mobileMenu = document.getElementById( 'menu' );


    doGsOrderTabs();

    jQuery( '#add_new_item' ).click( function( e ) {
        gsAddRowToTable();
        e.preventDefault();
    });


    var initialRows = jQuery( '#cost_table tbody tr' );
    if ( initialRows.length == 1 ) {
        gsAddRowToTable();
    }

    jQuery( '#cost_table tbody' ).on( 'change', '.umselect', function() {
        var selectedValue = jQuery( this ).val();
        var selectedParent = jQuery( this ).parent().parent();

        selectedParent.attr( 'data-unit-measure', selectedValue );
    });

    jQuery( '#cost_table tbody' ).on( 'mychange', '.sku-selector', function() {
        var selectedValue = jQuery( this ).attr( 'data-sku' );
        var selectedParent = jQuery( this ).parent().parent().parent();

        var isCustom = ( selectedValue == 'custom' || selectedValue == 's4s' || selectedValue == 'freeform' );

        selectedParent.find( 'input.qty' ).prop( 'disabled', 0 );
        selectedParent.removeClass( 'is-custom' ).removeClass( 'is-s4s' ).removeClass( 'is-freeform' ).removeClass( 'is-normal-sku' );
       
        if ( !isCustom ) {
            var params = {
                sku: selectedValue,
                quote_id: GSM.quote_id
            }

            gsmAdminAjax( 'lookup_sku', params, function( response ) {
                //alert( params.sku + response );
                var decodedResponse = jQuery.parseJSON( response );

                gsUpdateAllInfo( selectedParent, decodedResponse.info );

               // alert( 'updating pricing 6' );
                gsUpdateTablePricing();
            });

            selectedParent.find( 'input.qty' ).prop( 'disabled', false );

            selectedParent.addClass( 'is-normal-sku' );
        } else {
            gsClearFields( selectedParent );

            gsUpdateSkuInformation( selectedParent, selectedValue );
           
            gsEnableConfigureButton( selectedParent );

           // alert( 'updating pricing 7' );
            gsUpdateTablePricing(); 
        }
    });    

    jQuery( '#cost_table tbody' ).on( 'change', 'input.qty', function() {
       // 8' );
        var thisVal = jQuery( this ).val();
        if ( !jQuery.isNumeric( thisVal ) || thisVal == '0' ) {
            alert( 'Invalid quantity value, setting to 10' );
            jQuery( this ).val( '10' );
        }

        gsUpdateTablePricing();
    });

    /*
    jQuery( '#markup' ).change( function() {
        gsUpdateTablePricing();
        gsMaybeUpdateOverridePricing( false );

    });
    */

    jQuery( '.save-quote' ).click( function( e ) {
        if ( gsCheckCanSaveQuote() ) {
            doGsSaveQuote( jQuery( this ), function() {}, 1 );
        }
       
        e.preventDefault();
    });

    jQuery( '.update-pricing' ).click( function( e ) {
        if ( confirm( "This will save the quote, and then reload the quotation using the new pricing.  This will also convert all FIXED PRICE overrides back to DEFAULTS. \n\nAre you sure you wish to proceed (this cannot be undone)?" ) ) {
            doGsSaveQuote( jQuery( '.save-quote' ), function() {
                var params = {
                    quote_id: GSM.quote_id
                }
        
                gsmAdminAjax( 'update_pricing', params, function( response ) {
                    document.location.href = document.location.href;
                });        
            }, 0 );
        }

        e.preventDefault();
    });

    jQuery( '.duplicate-quote' ).click( function( e ) {
        if ( confirm( "This will duplicate the quote/order, and then reload the duplicated item. \n\nAre you sure you wish to proceed?" ) ) {
            doGsSaveQuote( jQuery( '.save-quote' ), function() {
                var params = {
                    quote_id: GSM.quote_id
                }
        
                gsmAdminAjax( 'duplicate_quote', params, function( response ) {
                    var decodedResponse = jQuery.parseJSON( response );
                    document.location.href = decodedResponse.url;
                });        
            }, 0 );
        }

        e.preventDefault();
    });

    jQuery( '.delete-quote' ).click( function( e ) {
        if ( !confirm( 'Are you sure you wish to delete the quote (this cannot be undone)?' ) ) {
            e.preventDefault();
        }
    });

    jQuery( '.open-existing' ).click( function( e ) {
        quoteID = jQuery( '#existing_quote' ).val();
        document.location.href = '/?gsm_quote_id=' + quoteID;

        e.preventDefault();
    });

    jQuery( '#customer_name' ).keyup( function() {
        var currentValue = jQuery( this ).val();
        if ( currentValue.length > 2 ) {
            jQuery( '.save-quote' ).prop( 'disabled', false );
        } else {
            jQuery( '.save-quote' ).prop( 'disabled', true );
        }
    });
    

    jQuery( 'table tbody ' ).on( 'click', 'a.delete-item', function( e ) {
        e.preventDefault();
        if ( confirm( 'Are you sure you want to delete this item?' ) == true ) {
            jQuery( this ).closest( 'tr').remove();
            gsRenumberRows();

           // alert( 'update pricing 9' );
            gsUpdateTablePricing();
            
        }
    }); 

    jQuery( 'table tbody ' ).on( 'click', 'a.duplicate-item', function( e ) {
        e.preventDefault();

        var entireRow = jQuery( this ).closest( 'tr' ).clone()
        entireRow.attr( 'data-markup', 'default' ).attr( 'data-custom-behavior', 'default' );
        jQuery( this ).closest( 'tbody' ).append( entireRow );
        jQuery( this ).closest( 'tbody' ).find( 'tr:last .dropdown-toggle' ).click();

        // Need to modify it to remove images
        entireRow.removeAttr( 'data-drawing-url' );
        entireRow.removeAttr( 'data-drawing-name' );

        gsRenumberRows();

        //alert( 'udating pricing 10' );
        gsUpdateTablePricing();
    }); 

    jQuery( '#cost_table' ).on( 'click', 'tr .sku-wrapper .dropdown li', function( e ) {
        var thisSku = jQuery( this ).attr( 'data-sku' );
        var thisName = jQuery( this ).html();

        //alert( 'setting sku to ' + thisSku );
        jQuery( this ).parent().parent().find( '.sku-selector' ).attr( 'data-sku', thisSku );
        //alert( 'settingValu to ' + thisName );
        jQuery( this ).parent().parent().find( '.sku-selector' ).val( thisName );
        jQuery( this ).parent().hide();

        jQuery( this ).parent().parent().find( '.sku-selector' ).trigger( 'mychange' );

        e.preventDefault();
    });

    jQuery( '#cost_table' ).on( 'focusin', 'tr .sku-selector', function() {
        jQuery( this ).parent().find( 'ul' ).show();
        jQuery( this ).keyup();
    });
    
    jQuery( '#cost_table' ).on( 'focusout', 'tr .sku-selector', function() {
        if ( jQuery( this ).parent().find( 'li:hover' ).length == 0 ) {
            jQuery( this ).parent().find( 'ul' ).hide();
        }  
    });

    jQuery( '#cost_table' ).on( 'keydown', 'tr .sku-selector', function( e ) {
        var code = e.key; // recommended to use e.key, it's normalized across devices and languages
        if ( code === "Enter" ) {
            e.preventDefault();
            e.stopPropagation();         
        }
    });
    
    jQuery( '#cost_table' ).on( 'keyup', 'tr .sku-selector', function( e ) {
        var currentValue = jQuery( this ).val().toUpperCase();
        jQuery( this ).parent().find( 'li' ).hide();
        jQuery( this ).parent().find( "li:contains('" + currentValue + "')" ).show();

        var skuInput = jQuery( this );
        var code = e.key; // recommended to use e.key, it's normalized across devices and languages
        if ( code === "Enter" ) {
            e.preventDefault();
            e.stopPropagation();

            jQuery( this ).parent().find( "li:contains('" + currentValue + "')" ).filter( function() {
                if ( jQuery( this ).text() == currentValue ) {
                    jQuery( this ).click();
                    skuInput.blur();
                }
            });           
        }
    });

    jQuery( '#master_markup' ).change( function() {
        //alert( 'update pricing 11 ' );
        gsUpdateTablePricing();
        gsCheckMarkupShowHide();
    });

    jQuery( '#freight_cost' ).change( function() {
       // alert( 'update pricing 12 ' );
        gsUpdateTablePricing();
        gsFrieghtShowHide();
        gsCheckSubtotalShowHide();
    }).change();
 //   gsFrieghtShowHide();

    jQuery( '#tax_rate' ).change( function() {
        if ( !gsmIsTaxable() ) {
           // jQuery( '#tax_rate' ).prop( 'disabled', true );
            jQuery( 'tr.tax' ).hide();
        } else {
          //  jQuery( '#tax_rate' ).prop( 'disabled', false );
            jQuery( 'tr.tax' ).show();
        }

        gsCheckSubtotalShowHide();
        gsUpdateTablePricing();
    }).change();

    gsCheckMarkupShowHide();
    gsCheckSubtotalShowHide();

    gsRenumberRows();
    gsUpdateAllRows();


    jQuery( 'a.convert-order' ).click( function( e ) {
        e.preventDefault();

        var convertButton = jQuery( this );

        var button = jQuery( 'button.save-quote' );
        doGsSaveQuote( button, function() {
            document.location.href = convertButton.attr( 'href' );
        });
    });

    if ( GSM.is_order == 1 && false ) {
        jQuery( '.top-area-wrap input, .top-area-wrap select' ).prop( 'disabled', true );
        jQuery( '#cost_table input' ).prop( 'disabled', true );
        jQuery( '#cost_table button' ).hide();
        jQuery( 'span.delete-item' ).hide();
        jQuery( '#customer_notes, #mill_notes' ).prop( 'disabled', true );
    }

    jQuery( 'body' ).on( 'click', '#sku_lookup_modal a.add_to_quote', function( e ) {
        e.preventDefault();

        var sku = jQuery( this ).attr( 'data-sku' );
        var id = jQuery( this ).attr( 'data-id' );
        gsAddRowToTable();

        var lastRow = jQuery( '#cost_table tbody tr:last' );
        if ( lastRow.length ) {
            lastRow.find( 'input.sku-selector' ).val( sku ).attr( 'data-sku', id ).trigger( 'mychange' );
        }

        jQuery( "#sku_lookup_close" ).click();    
    });

    jQuery( '#cost_table' ).on( 'change', 'input.flip-active', function( e ) {
        var thisRow = jQuery( this ).closest( 'tr' );
        var isChecked = jQuery( this ).is( ':checked' );
        
        if ( isChecked ) {
            thisRow.attr( 'data-is-active', 1 );
        } else {
            thisRow.attr( 'data-is-active', 0 );
        }

        // alert( 'updating pricing 1' );
        gsUpdateTablePricing();
    });

    // Sortable
    if ( jQuery( '#cost_table' ).length ) {
        jQuery( '#cost_table tbody' ).sortable({
            items: 'tr.not-dummy',
            handle: '.handle',
            axis: 'y',
            cursor: "move",
            forceHelperSize: true,
            forcePlaceholderSize: true,
            update: function() {
                gsRenumberRows();
            }
        });

        // seemes to be deprecated
        //.disableSelection();
    }
}

jQuery( document ).ready( function() { doGSready(); } );

/// VUE CODE

function gsHelperFindParentRow( element ) {
    return jQuery( element ).closest( 'tr' );
}

function gsHelperLocateRowByNum( number ) {
    return jQuery( 'tr[data-num="' + number + '"]' );
}

function gsSetupVue() {
    // ***********************************
    // UNIT OF MEASURE BOX
    // ***********************************
    
    if ( jQuery( '#vuearea' ).length ) {
        var app = new Vue({
        el: '#vuearea',
        data: {
            row: 0,
            unit: 'lf',
            length: 0,
            modal: false
        },
        methods: {
            canShowLength() {
                return this.unit == 'pc';
            }
        },
        mounted() {
            var vueObject = this;

            this.modal = new bootstrap.Modal( document.getElementById( 'um_modal' ) );

            jQuery( '#cost_table' ).on( 'click', 'button.open-um', function() {
                var parentRow = gsHelperFindParentRow( this );
                app.row = parentRow.attr( 'data-num' );
        
                app.unit = parentRow.attr( 'data-unit-measure' );
                app.length = parentRow.attr( 'data-pc-length' );
        
                vueObject.modal.show();
            });
        
            jQuery( '#um_modal .modal-footer button' ).click( function() {
                var row = gsHelperLocateRowByNum( app.row );

                var canSave = true;
                if ( app.unit == 'pc' && app.length <= 0 ) {
                    canSave = false;
                    alert( 'There is an problem with the per-piece length you have entered' );
                }
        
                if ( canSave ) {
                    row.attr( 'data-unit-measure', app.unit );
                    row.attr( 'data-pc-length', app.length );
            
                    vueObject.modal.hide();
                    // alert( 'update pricing 2' );
                    gsUpdateTablePricing();
                }
            });
        }
    });
    }


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

    // ***********************************
    // FRONT PAGE SKU LOOKUP
    // ***********************************

    if ( jQuery( '#sku_lookup_modal' ).length ) {
        var sku_lookup = new Vue({
            el: '#sku_lookup_modal',
            data: {
                sku: '',
                results: [],
                timer: false,
                thickness: 0,
                width: 0
            },
            methods: {
                actualSkuChange() {
                    var params = {
                        sku: this.sku,
                        width: this.width,
                        thickness: this.thickness
                    }
                  // alert( this.width  + ' ' + this.sku + ' ' + this.thickness );
            
                    var thisContext = this;

                    var oldText = jQuery( 'h6#search_sku_title' ).html();
                    jQuery( 'h6#search_sku_title' ).html( 'Loading, please wait...' );

                    jQuery( 'button#search_sku_button' ).prop( 'disabled', true );

                    gsmAdminAjax( 'sku_search', params, function( response ) {
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;

                        jQuery( 'h6#search_sku_title' ).html( oldText );
                        jQuery( 'button#search_sku_button' ).prop( 'disabled', false );
                    });

                    this.timer = false;
                },
                onSkuChange() {
                    if ( this.timer ) {
                        clearTimeout( this.timer );
                    }

                    this.timer = setTimeout( this.actualSkuChange, 100 );   
                }
            },
            mounted() {
                jQuery( 'button.fp-sku-lookup' ).click( function( e ) {
                    e.preventDefault();
                
                    var showAddData = jQuery( this ).attr( 'data-show-add' );
                    if ( showAddData == 1 ) {
                        jQuery( '#sku_lookup_modal' ).removeClass( 'hide-add-column' );
                    } else {
                        jQuery( '#sku_lookup_modal' ).addClass( 'hide-add-column' );
                    }
        
                    var myModal = new bootstrap.Modal(document.getElementById('sku_lookup_modal'));
                    myModal.show();
                });
            }
        });
    }

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
   
 
   // ***********************************
    // QUOTE LOG
    // ***********************************

    var foundSection = jQuery( '#quote_log_vue_area' );
    if ( foundSection.length ) {
        var fp_search = new Vue({
            el: '#quote_log_vue_area',
            data: {
                quote_id: 0,
                results: []
            },
            methods: {
                loadItems() {
                    var oldHtml = jQuery( '#quote_log h6' ).html();
                    jQuery( '#quote_log h6' ).html( 'Loading items, please wait...' );

                    var params = {
                        quote_id: this.quote_id
                    }

                    var thisContext = this;

                    gsmAdminAjax( 'get_quote_log', params, function( response ) {
                       // alert( response );
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;

                        jQuery( '#quote_log h6' ).html( oldHtml );
                    });
                }
            },
            mounted() {
                var context = this;

                jQuery( 'button.view-quote-log' ).click( function( e ) {
                    context.quote_id = jQuery( this ).attr( 'data-quote-id' );
                    context.loadItems();

                    var myModal = new bootstrap.Modal(document.getElementById( 'quote_log' ) );
                    myModal.show();
        
                    e.preventDefault();
            
                    jQuery( '#quote_log .modal-footer button' ).click( function() {
                        myModal.hide();
                    });
                });
            }
        });
    }

    // ***********************************
    // FRONT PAGE SEARCH QUOTE
    // ***********************************
    var foundSection = jQuery( '#quote_vue_area' );
    if ( foundSection.length ) {
        var fp_search = new Vue({
            el: '#quote_vue_area',
            data: {
                show_number: true,
                show_date: true,
                show_gslp_po: false,
                show_cust_po: false,
                show_status: false,
                show_branch: true,
                show_contact: true,
                show_salesperson: true,
                author: 0,
                company: 0,
                period: 6,
                branch: GSM.user_location,
                type: 'quote',
                results: [],
                nonce: '',
                searchText: '',
                searching: false
            },
            methods: {
                resetDialog() {
                    this.show_number = true;
                    this.show_date = true;
                    this.show_gslp_po =  false;
                    this.show_cust_po =  false;
                    this.show_status =  false;
                    this.show_branch =  true;
                    this.show_contact =  true;
                    this.show_salesperson =  true;
                    this.author =  0;
                    this.company =  0;
                    this.period =  6;
                    this.branch =  GSM.user_location;
                    this.type =  'quote';
                    this.results =  [];
                    this.nonce =  '';
                    this.searchText =  '';
                    this.searching = false;
                },
                onAuthorChange() {
                    this.loadItems();
                },
                onCompanyChange() {
                    this.loadItems();
                },
                onBranchChange() {
                    this.loadItems();
                },
                onPeriodChange() {
                    this.loadItems();
                },
                handleSearch( e ) {
                    e.preventDefault();
                    this.loadItems();
                },
                isDoingSearch() {
                    return ( this.searchText.length > 0 );
                },
                searchReset( e ) {
                    e.preventDefault();
                    this.searchText = '';
                    this.loadItems();
                },
                onTypeChange() {
                    if ( this.type == "quote" ) {
                        this.show_gslp_po = false;
                        this.show_cust_po = false;
                        this.show_status = false;
                        this.show_salesperson = true;
                        this.show_contact = true;
                    } else if ( this.type == "order" ) {
                        this.show_gslp_po = true;
                        this.show_cust_po = true;
                        this.show_status = true;
                        this.show_salesperson = false;
                        this.show_contact = false;
                    } else {
                        this.show_gslp_po = false;
                        this.show_cust_po = false;
                        this.show_status = false;
                        this.show_salesperson = true;
                        this.show_contact = true;
                    }
                    this.results = [];

                    this.loadItems();
                },
                changeOrderStatus( event ) {
                    var thisVariable = jQuery( event.target );

                    var newValue = thisVariable.val();
                    var quoteID = thisVariable.attr( 'data-num' );

                    var params = {
                        id: quoteID,
                        new_status: newValue,
                    }

                    var thisContext = this;

                    thisVariable.attr( 'data-status', newValue );

                    gsmAdminAjax( 'update_order_status', params, function( response ) {
                        
                        //alert( response );
                       // var decodedResponse = jQuery.parseJSON( response );
                       alert( 'Order status has been changed' );
                    });
                },
                modalHidden() {
                    alert( 'hidden' );
                },
                loadItems() {
                    jQuery( '#quote_search_modal h6' ).html( 'Loading items, please wait...' );

                    var params = {
                        author: this.author,
                        company: this.company,
                        period: this.period,
                        branch: this.branch,
                        type: this.type,
                        searchText: this.searchText
                    }
                    
                    this.searching = ( this.searchText.length );

                    var thisContext = this;
                    this.nonce = GSM.nonce;

                    jQuery( 'table.search-table' ).css( 'opacity', '0.5' );

                    gsmAdminAjax( 'quote_search', params, function( response ) {
                        //alert( response );
                        var decodedResponse = jQuery.parseJSON( response );
                        thisContext.results = decodedResponse.data;

                        jQuery( '#quote_search_modal h6' ).html( 'Search Quotes And Orders' );

                        jQuery( 'table.search-table' ).css( 'opacity', '1.0' );
                    });
                }
            },
            mounted() {
                var thisContext = this;
                var myModal = 0;

                thisContext.resetDialog();
                thisContext.loadItems();

                jQuery( 'button.fp-search-quotes' ).click( function( e ) {
                    if ( myModal == 0 ) {
                        var thisModal = new bootstrap.Modal( document.getElementById( 'quote_search_modal' ) );
                        myModal = thisModal;
                        jQuery( '#quote_search_modal' ).on( 'hidden.bs.modal', function() {  
                            thisContext.resetDialog();
                            thisContext.loadItems();
                        });
                    }
                    myModal.show();
        
                    e.preventDefault();
            
                    jQuery( '#quote_search_modal .modal-footer button' ).click( function() {
                        thisContext.resetDialog();
                        thisContext.loadItems();
                        
                        myModal.hide();
                    });
                });
            }
        });
    }

    // ***********************************
    // EMAIL DIALOG
    // ***********************************

    if ( jQuery( '#email_modal' ).length ) {
        var email_box = new Vue({
            el: '#email_modal',
            data: {
                send_address: 'test@duane.com',
                email_content: 'text content',
                customer: true,
                order: false,
                send_to_me: false,
                modal: false
            },
            methods: {
                onSendEmail() {
                    var vueObject = this;
                    var sendToEmail = this.send_address; 

                    if ( sendToEmail.length ) {
                        var button = jQuery( 'button.send-button' );
                        button.html( 'Sending...' );
                
                        var sendToMe = ( this.send_to_me ) ? 1 : 0;
                        var isMill = ( this.customer == false ) ? 1 : 0;

                        var params = {
                            quote_id: GSM.quote_id,
                            send_to_me: sendToMe,
                            send_address: sendToEmail,
                            email_content: this.email_content,
                            is_mill: isMill
                        }
                
                        gsmAdminAjax( 'send_quote_email', params, function( response ) {
                            vueObject.modal.hide();
                            button.html( 'Send' );
                        });
                    }
                },
                updateEmailInvalid() {
                    //var sendToEmail = jQuery( '#send_address' ).val();    
                    if ( this.send_address.length ) {
                        jQuery( '#send_address' ).removeClass( 'is-invalid' );
                    } else {
                        jQuery( '#send_address' ).addClass( 'is-invalid' );
                    }
                },
                setupEmailModal() {
                    var thisVueObject = this;
                    
                    this.updateEmailInvalid();

                    this.order = ( GSM.is_order == 1 );
                    this.send_to_me = false;

                    if ( this.customer ) {
                        if ( this.order ) {
                            // customer order
                            this.email_content = GSM.emails.customer_order;
                        } else {
                            this.email_content = GSM.emails.customer_quote;
                        }
                    } else {
                        this.email_content = GSM.emails.mill_order;               
                    }

                    var button = jQuery( 'button.save-quote' );
                //    console.log( 'Saving quote....' );
                    doGsSaveQuote( button, function() {           
                    //    console.log( 'Done saving quote....' );  
                        thisVueObject.modal.show();
                    });
                }
            },
            mounted() {
                var vueObject = this;
                jQuery( '.send-email' ).click( function( e ) {
                    e.preventDefault();

                    vueObject.customer = true;
                    vueObject.send_address = jQuery( '#email' ).val();

                    vueObject.setupEmailModal();

                });

                jQuery( '.send-mill-email' ).click( function( e ) {
                    e.preventDefault();

                    vueObject.customer = false;
                    vueObject.send_address = 'orders@gsmillwork.com';

                    vueObject.setupEmailModal();
                });

                vueObject.modal = new bootstrap.Modal( document.getElementById( 'email_modal' ));
            }
        });
    }


    // ***********************************
    // DUAL EMAIL DIALOG
    // ***********************************

    if ( jQuery( '#dual_email_modal' ).length ) {
            var email_box = new Vue({
            el: '#dual_email_modal',
            data: {
                send_to_mill: true,
                send_to_cust: true,
                send_address_cust: 'test@duane.com',
                email_content_cust: 'text content',
                send_address_mill: 'test@duane.com',
                email_content_mill: 'text content',
                send_to_me: false,   
                modal: false,
                sending: 0
            },
            methods: {
                checkIfDone() {
                    if ( this.sending == 0 ) {
                        this.modal.hide();
                        jQuery( 'button.send-button' ).html( 'Send' );
                        document.location.href = GSM.order_url + '/?gsm_quote_id=' + GSM.quote_id;
                    }
                },
                onCancelOrClose() {
                    this.modal.hide();
                    document.location.href = GSM.order_url + '/?gsm_quote_id=' + GSM.quote_id;
                },
                onSendDualEmail() {
                    var canSend = true;
                    var vueObject = this;

                    if ( this.send_to_cust && this.send_address_cust.length == 0 ) {
                        alert( 'Customer email address cannot be blank' );
                        canSend = false;
                    }

                     if ( this.send_to_mill && this.send_address_mill.length == 0 ) {
                        alert( 'Mill email address cannot be blank' );
                        canSend = false;
                    }
                    
                    if ( canSend ) {
                        var button = jQuery( 'button.send-button' );
                        button.html( 'Sending...' );
                
                        if ( this.send_to_cust ) {
                            // Customer
                            var params = {
                                quote_id: GSM.quote_id,
                                send_to_me: ( this.send_to_me ) ? 1 : 0,
                                send_address: this.send_address_cust,
                                email_content: this.email_content_cust,
                                is_mill: 0
                            }

                            this.sending++;
                            gsmAdminAjax( 'send_quote_email', params, function( response ) {
                                vueObject.sending--;
                                vueObject.checkIfDone();
                            });
                        }

                        if ( this.send_to_mill ) {
                            var params = {
                                quote_id: GSM.quote_id,
                                send_to_me: ( this.send_to_me ) ? 1 : 0,
                                send_address: this.send_address_mill,
                                email_content: this.email_content_mill,
                                is_mill: 1
                            }
            
                            this.sending++;
                            gsmAdminAjax( 'send_quote_email', params, function( response ) {
                                vueObject.sending--;
                                vueObject.checkIfDone();
                            });
                        }
                    }
                },
                setupEmailModal() {
                    this.send_address_cust = jQuery( '#email' ).val();
                    this.email_content_cust = GSM.emails.customer_order;
                    this.send_address_mill = 'orders@gsmillwork.com';
                    this.email_content_mill = GSM.emails.mill_order;
                    
                    this.modal.show();
                }
            },
            mounted() {
                var vueObject = this;
                vueObject.modal = new bootstrap.Modal( document.getElementById( 'dual_email_modal' ), { keyboard: true, backdrop: 'static' } );

                document.getElementById( 'dual_email_modal' ).addEventListener( 'hidden.bs.modal', function (event) {
                    document.location.href = GSM.order_url + '/?gsm_quote_id=' + GSM.quote_id;
                });
                
                if ( GSM.show_dual_email == 1 ) {
                    vueObject.setupEmailModal();
                }
            }
        });
    }

    // ***********************************
    // TABLE OPTIONS DIALOG
    // ***********************************

    if ( jQuery( '#options_override_vue' ).length ) {
        var options_dialog = new Vue({
            el: '#options_override_vue',
            data: {
                modal: false,
                parent_row: false,
                sku: '',
                is_custom_or_s4s: false,
                species: 'poplar',
                custom_species: '',
                finish: 'unfinished',
                custom_thickness: '',
                custom_width: '',
                custom_desc: '',
                custom_sku: '',
                attach_file: '',
                knife_charge: 1,
                edge_glue: 0,
                line_notes: '',
                original_species: '',
                original_ripsku: '',
                original_thickness: '',
                original_width: '',
                original_finish: '',
                drawing_name: '',
                drawing_url: '',
                species_note: '',
                setup_charge_type: '',
                include_setup_charge: 1,
                priming_cost_per_um: GSM.config.primed_cost,

                overrides_modal: false,
                markup: 0,
                gsm_adjust: '1.00',
                ripsku: '',
                custom_behaviour: 'default',

                // saved for the item
                pieces: 1,
                override_finish: 0,
                override_edge: 0,
                override_setup: 0,
                override_knife: 0,
                override_charge: 0,
                override_gsm: 0,
                base_charge: 0,
                gsm_charge: 0,
                original_gsm_charge: 0,
                quantity: 0,
                unit_measure: 'lf',
                edge_glue_cost: 0,
                finish_cost: 0,
                knife_cost: 0,
                setup_cost: 0,
                gsl_cost: 0,
                base_cost: 0,
                full_gsm_cost: 0
            },
            computed: {
                gsmAdjustDisabled() {
                   return ( this.custom_behaviour == 'override-final' || GSM.is_sales_manager == '0' );
                }
            },
            methods: {
                getAttrOrUseDefault( attr, def ) {
                    var variable = this.parent_row.attr( attr );
                    if ( variable && variable.length ) {
                        return variable;
                    } else {
                        return def;
                    }
                },
                setAttr( attr, value ) {
                    this.parent_row.attr( attr, value );
                },
                clearAttr( attr ) {
                    this.parent_row.attr( attr, '' );
                },
                disableUnavailableSpecies( allSpecies ) {
                    if ( allSpecies ) {
                        var allSplit = allSpecies.split( ' ' );
            
                        jQuery( '#species option' ).prop( 'disabled', true );
                        jQuery.each( allSplit, function( index, value ) {
                            jQuery( '#species option.' + value ).prop( 'disabled', false );
                        });

                        jQuery( '#species option.custom' ).prop( 'disabled', false );
                    }
                },
                resetOverrides() {
                    gsmLogMessage( 'Overriding defaults as something has changed' );
                    if ( this.species == 'custom' ) {
                        this.setAttr( 'data-custom-behavior', 'override-final' ); 
                        this.setAttr( 'data-override-charge', '0.00' );
                        this.setAttr( 'data-override-gsm', '0.00' );
                    } else {
                        this.setAttr( 'data-custom-behavior', 'default' );
                        this.setAttr( 'data-markup', 'default' );
                        this.setAttr( 'data-gsm-adjust', '1.00' );
                        this.clearAttr( 'data-override-charge' );
                        this.clearAttr( 'data-override-gsm' );

                        gsmLogMessage( 'cleared defaults' );
                    }
                },
                updateRowAndPricing() {
                    gsUpdateRowInformation( this.parent_row );
                    gsEnableOverrideButton( this.parent_row );

                   // alert ( 'update pricing 3' );
                    gsUpdateTablePricing();
                },
                setupCustomS4S() {
                    this.sku = this.parent_row.find( '.sku-selector' ).attr( 'data-sku' )
                    this.is_custom_or_s4s = this.sku == 's4s' || this.sku == 'custom';
                },
                edgeReadOnly() {
                    return ( this.custom_behaviour == 'individual-override' );
                },
                computeOverridePriceDefaults() {
                    this.finish_cost = 0;
                    this.knife_cost = 0;
                    this.setup_cost = 0;
                    this.edge_glue_cost = 0;
                    this.base_cost = 0;

                    var internalQuantity = this.quantity;
                    if ( this.unit_measure == 'pc' ) {
                        internalQuantity = internalQuantity * this.getAttrOrUseDefault( 'data-pc-length' );
                    }           
                    
                    if ( this.is_custom_or_s4s ) {
                        // This fails when it's a custom wood
                        var ripThick = jQuery( '#ripsku option[value=\'' + this.ripsku + '\']' ).attr( 'data-thickness' );
                        var ripFactor = jQuery( '#ripsku option[value=\'' + this.ripsku + '\']' ).attr( 'data-factor' );
                        var ripBaseCost = this.getAttrOrUseDefault( 'data-custom-thickness-price-' + ripThick );
                        var baseCost = ripBaseCost * ripFactor;
                        
                        this.base_cost = baseCost/1000.0;
                    } else {         
                        baseCost = parseFloat( this.getAttrOrUseDefault( 'data-species-price-' + this.species ) );
                        this.base_cost = baseCost;
                    }

                    this.base_cost = parseFloat( this.base_cost ).toFixed( 2 );
                    this.base_charge = parseFloat( this.base_cost ).toFixed( 2 );

                // alert( this.edge_glue );
                    if ( this.edge_glue == '1' ) {
                        this.edge_glue_cost = this.base_cost * GSM.config.edge_glue_factor;

                        this.edge_glue_cost = gsTruncateNumber( this.edge_glue_cost, 5 );
                    }

                    var finishType = this.getAttrOrUseDefault( 'data-finish' );
                    if ( finishType == 'primed' ) {
                        this.finish_cost = this.priming_cost_per_um;
                    } else if ( finishType == 'clear-coat' ) {
                        this.finish_cost = GSM.config.clearcoat_cost;
                    }

                    if ( this.knife_charge == '1' ) {
                    //  alert( this.knife_charge );
                        if ( this.sku == 'custom' ) {
                            this.knife_cost = Math.ceil( this.getAttrOrUseDefault( 'data-custom-width' ) ) * GSM.config.knife_charge;

                            this.knife_cost = gsTruncateNumber( this.knife_cost, 2 );
                        }
                    } 

                    var roughCost = this.base_cost * internalQuantity;
                    
                    if ( this.sku == 'custom'  ) {
                        if ( roughCost < GSM.config.setup_charge_threshold ) {
                            this.setup_cost = GSM.config.custom_setup_charge;
                        }
                    } else if ( this.sku == 's4s' ) {
                        if ( roughCost < GSM.config.setup_charge_threshold ) {
                            this.setup_cost = GSM.config.s4s_setup_charge;
                        }
                    } else {
                        // Normal SKUs
                        if ( roughCost < GSM.config.setup_charge_threshold ) {
                        // alert( this.setup_charge_type );
                            if ( this.setup_charge_type == 's4s' ) {
                                this.setup_cost = GSM.config.s4s_setup_charge;  
                            } else {
                                this.setup_cost = GSM.config.custom_setup_charge;
                            }
                        } else {
                            this.setup_cost = 0;
                        }
                    }  

                    if ( this.include_setup_charge == 0 ) {
                        this.setup_cost = 0;
                    }    

                //   console.log( roughCost + ' ' + this.setup_cost );
                },
                updateOverridePricing() {
                    switch( this.custom_behaviour ) {
                        case 'default':
                        // alert( 'default' );
                            this.overrideDefaults();

                            this.gsl_cost = this.full_gsm_cost * this.getMarkupFactor();
                            this.override_charge = parseFloat( this.gsl_cost / this.quantity ).toFixed( 2 );

                            console.log( 'DEFAULT baseCost ' + this.base_cost + ', edgeGlueCost: ' + this.edge_glue_cost + ', finishCost: ' + this.finish_cost + ', setupCost: ' + this.setup_cost + ' ' + this.knife_cost + ' ' + this.gsm_charge + ' ' + this.gsl_cost + ' ' + this.override_charge + ' ' + this.getMarkupFactor() );

                        //  alert( this.override_charge );
                            break;
                        case 'override-individual':
                            this.finish_cost = this.getAttrOrUseDefault( 'data-override-finish', this.finish_cost );
                        
                            this.setup_cost = this.getAttrOrUseDefault( 'data-override-setup', this.setup_cost );

                            if ( this.edge_glue == '1' ) {
                                this.edge_glue_cost = this.getAttrOrUseDefault( 'data-override-edge',  this.edge_glue_cost );
                            } else {
                                this.edge_glue_cost = 0;
                            }

                            if ( this.knife_charge == '1' ) {
                                this.knife_cost = this.getAttrOrUseDefault( 'data-override-knife', this.knife_cost  );
                            } else {
                                this.knife_cost = 0;
                            }

                        // console.log( this.base_cost + ' ' + this.override_setup + ' ' + this.override_finish + ' ' + this.override_knife + ' ' + this.override_edge + ' ' + this.gsm_charge + ' ' + this.gsl_cost + ' ' );

                            this.overrideDefaults();

                            this.gsl_cost = this.full_gsm_cost * this.getMarkupFactor();
                            this.override_charge = parseFloat( this.gsl_cost / this.quantity ).toFixed( 2 );
                        // alert( this.gsl_cost );

                            console.log( this.base_cost + ' ' + this.override_setup + ' ' + this.override_finish + ' ' + this.override_knife + ' ' + this.override_edge + ' ' + this.gsm_charge + ' ' + this.gsl_cost + ' ' + this.override_charge + ' ' + this.getMarkupFactor() );
                            break;
                        case 'override-final':
                            this.overrideDefaults();

                            this.gsm_charge = this.getAttrOrUseDefault( 'data-override-gsm', this.gsm_charge );
                            this.gsl_cost = this.getAttrOrUseDefault( 'data-override-charge', parseFloat( this.full_gsm_cost * this.getMarkupFactor() / this.quantity ).toFixed( 2 ) );
                            this.override_charge = this.gsl_cost;

                            console.log( 'OF => ' + this.base_cost + ' ' + this.override_setup + ' ' + this.override_finish + ' ' + this.override_knife + ' ' + this.override_edge + ' ' + this.gsm_charge + ' ' + this.gsl_cost + ' ' );
                            break;
                    }
                
                },
                overrideDefaults() {
                //  alert( 'setting defaults' );
                    this.full_gsm_cost = 0;

                    this.override_finish = parseFloat( this.finish_cost ).toFixed( 2 );
                    this.override_knife = parseFloat( this.knife_cost ).toFixed( 2 );
                    this.override_setup = parseFloat( this.setup_cost ).toFixed( 2 );
                    this.override_edge = parseFloat( this.edge_glue_cost ).toFixed( 2 );

                    var internalQuantity = this.quantity;
                    if ( this.unit_measure == 'pc' ) {
                        internalQuantity = internalQuantity * this.getAttrOrUseDefault( 'data-pc-length' );
                    }  

                    var total_cost = ( parseFloat( this.base_cost ) + parseFloat( this.override_finish ) + parseFloat( this.override_edge ) + ( parseFloat( this.override_knife ) + parseFloat( this.override_setup ) ) / internalQuantity );

                    var quantity = this.quantity;
                    if ( this.unit_measure == 'pc' ) {
                        total_cost = total_cost * this.getAttrOrUseDefault( 'data-pc-length' );
                    }       

                    this.full_gsm_cost = total_cost * this.quantity * parseFloat( this.gsm_adjust );

                    this.gsm_charge = parseFloat( this.full_gsm_cost / this.quantity ).toFixed( 2 );
                  
                    // Adjustment charge
                    //this.gsm_charge = this.gsm_charge * this.gsm_adjust;

                    this.original_gsm_charge = this.gsm_charge;
                    
                },
                recomputeOverridesDueToChange() {
                    this.computeOverridePriceDefaults();
                    this.updateOverridePricing();
                },
                recomputeOnly() {
                    this.computeOverridePriceDefaults();

                    this.full_gsm_cost = 0;

                    var internalQuantity = this.quantity;
                    if ( this.unit_measure == 'pc' ) {
                        internalQuantity = internalQuantity * this.getAttrOrUseDefault( 'data-pc-length' );
                    }       

                    var total_cost = ( parseFloat( this.base_cost ) + parseFloat( this.override_finish ) + parseFloat( this.override_edge ) + ( parseFloat( this.override_knife ) + parseFloat( this.override_setup ) ) / internalQuantity );
                    if ( this.unit_measure == 'pc' ) {
                        total_cost = total_cost * this.getAttrOrUseDefault( 'data-pc-length' );
                    }       

                    this.full_gsm_cost = total_cost * this.quantity * parseFloat( this.gsm_adjust );
                    this.gsm_charge = parseFloat( this.full_gsm_cost / this.quantity ).toFixed( 2 );

                    // Adjustment charge
                   // this.gsm_charge = this.gsm_charge * parseFloat( this.gsm_adjust );

                    this.original_gsm_charge = this.gsm_charge;

                    switch( this.custom_behaviour ) {
                        case 'default':
                        case 'override-individual':
                            this.gsl_cost = this.full_gsm_cost * this.getMarkupFactor();

                            this.override_charge = parseFloat( this.gsl_cost / this.quantity ).toFixed( 2 );
                            break;
                        case 'override-final':
                            break;
                    }
                },
                fixAttributes() {
                    switch( this.custom_behaviour ) {
                        case 'default':
                            this.parent_row.removeAttr( 'data-override-finish' );
                            this.parent_row.removeAttr( 'data-override-knife' );
                            this.parent_row.removeAttr( 'data-override-setup' );
                            this.parent_row.removeAttr( 'data-override-edge' );
                            this.parent_row.removeAttr( 'data-override-gsm' );
                            this.parent_row.removeAttr( 'data-override-charge' );

                            break;
                        case 'override-individual':
                            this.parent_row.removeAttr( 'data-override-charge' );
                            this.parent_row.removeAttr( 'data-override-gsm' );

                            if ( this.knife_charge == '0' ) {
                                this.parent_row.removeAttr( 'data-override-knife' );
                            }

                            if ( this.edge_glue == '0' ) {
                                this.parent_row.removeAttr( 'data-override-edge' ); 
                            }
        
                            break;
                        case 'override-final':
                            this.parent_row.removeAttr( 'data-override-finish' );
                            this.parent_row.removeAttr( 'data-override-knife' );
                            this.parent_row.removeAttr( 'data-override-setup' );
                            this.parent_row.removeAttr( 'data-override-edge' );
                            
                            break;

                    }
                },
                getMarkupFactor() {
                    var calculatedMarkup;
                
                    if ( this.markup == 'default' ) {
                        var masterMarkup = jQuery( '#master_markup' ).val();
                        if ( masterMarkup == 'auto' ) {
                            calculatedMarkup = jQuery( '#calculated_markup' ).val();
                        } else {
                            calculatedMarkup = masterMarkup;
                        }
                    } else {
                        // The markup is determine by the override select
                        calculatedMarkup = this.markup;
                    } 
                
                    return calculatedMarkup;
                },
                handleFileUpload( event ) {
                    console.log( event.target.files );

                    var selected_image = event.target.files[0];
                    var options = {
                        url: GSM.admin_url,
                        type: "POST",
                        processData: false, 
                        contentType: false
                    }

                    // create a form
                    const form = new FormData();
                    form.append( 'file', selected_image );
                    form.append( 'action', 'handle_ajax' );
                    form.append( 'gsm_action', 'add_image_to_quote' );
                    form.append( 'gsm_nonce' , GSM.ajax_nonce );
                    form.append( 'quote_id', GSM.quote_id );
                    // submit the image

                    var vueObject = this;
                    options.data = form;
                    options.success = function( data ) {
                        decode = jQuery.parseJSON( data );
                        if ( decode.success == 1 ) {
                            vueObject.drawing_name = decode.name;
                            vueObject.drawing_url = decode.url;
                        }
                    }

                jQuery.ajax( options );

                /*
                    $.ajax(Object.assign({}, this.options, {data: form}))
                    .then(this.createImage)
                    .catch(this.onImageError);
                    */

                },
                deleteImage() {
                    this.drawing_url = '';
                    this.drawing_name = '';
                    jQuery( '#attach_file' ).val( '' );
                },
                refreshNotes() {
                    if ( this.species != 'custom' ) {
                        this.species_note = jQuery( '#species option.' + this.species ).attr( 'data-notes' ).replace( /\n/g, "<br>" );
                    }   
                }
            },
            mounted() {
                // When the option button is clicked
                var vueObject = this;

                jQuery( '#cost_table tbody' ).on( 'click', 'button.options', function( e ) {
                    vueObject.modal = new bootstrap.Modal( document.getElementById( 'options_modal' ));
                    vueObject.parent_row = jQuery( this ).closest( 'tr' );

                    vueObject.setupCustomS4S();

                    vueObject.original_species = vueObject.getAttrOrUseDefault( 'data-species', '' );
                    vueObject.species = vueObject.getAttrOrUseDefault( 'data-species', 'poplar' );
                    vueObject.custom_species = vueObject.getAttrOrUseDefault( 'data-species-custom', '' );
                    vueObject.finish = vueObject.getAttrOrUseDefault( 'data-finish', 'unfinished' );
                    vueObject.original_finish = vueObject.getAttrOrUseDefault( 'data-finish', '' );
                    vueObject.custom_desc = vueObject.getAttrOrUseDefault( 'data-custom-desc', '' );
                    vueObject.custom_sku = vueObject.getAttrOrUseDefault( 'data-custom-sku', '' );
                    vueObject.line_notes = vueObject.getAttrOrUseDefault( 'data-notes', '' );
        
                    vueObject.refreshNotes();
                    //alert( vueObject.species_note );

                    // The file upload box
                    jQuery( '#attach_file' ).val( '' );
                    vueObject.drawing_url = vueObject.getAttrOrUseDefault( 'data-drawing-url', '' );
                    vueObject.drawing_name = vueObject.getAttrOrUseDefault( 'data-drawing-name', '' );

                    // We should setup the custom fields here
                    if ( vueObject.is_custom_or_s4s ) {
                        vueObject.custom_width = vueObject.getAttrOrUseDefault( 'data-custom-width', '' );
                        vueObject.custom_thickness = vueObject.getAttrOrUseDefault( 'data-custom-thickness', '' );

                        // Save these to check for a change later
                        vueObject.original_thickness = vueObject.custom_thickness;
                        vueObject.original_width = vueObject.custom_width;
                        vueObject.original_ripsku = vueObject.getAttrOrUseDefault( 'data-custom-ripsku', '' );    
                    }

                    if ( vueObject.sku == 'custom' ) {
                        jQuery( '#custom_thickness option.for-custom' ).show();
                    } else {
                        jQuery( '#custom_thickness option.for-custom' ).hide();
                    }

                    var allSpecies = vueObject.getAttrOrUseDefault( 'data-all-species', false );
                    vueObject.disableUnavailableSpecies( allSpecies );

                    vueObject.modal.show();
                });

                // When the options dialog is closed
                jQuery( '#options_modal .modal-footer button' ).on( 'click', function (e) {
                    resetOverrides = false;
                    updatingRipsku = false;

                    // Need to determine whether we've set this up before

                    // Update the species
                    vueObject.setAttr( 'data-species', vueObject.species );

                    // Update the custom species
                    if ( vueObject.species == 'custom' ) {
                        vueObject.setAttr( 'data-species-custom', vueObject.custom_species );
                    } else {
                        vueObject.clearAttr( 'data-species-custom' );
                    }

                    if ( vueObject.drawing_url ) {
                        vueObject.setAttr( 'data-drawing-url', vueObject.drawing_url );
                        vueObject.setAttr( 'data-drawing-name', vueObject.drawing_name );
                    } else {
                        vueObject.parent_row.removeAttr( 'data-drawing-url' );
                        vueObject.parent_row.removeAttr( 'data-drawing-name' );
                    }

                    vueObject.setAttr( 'data-finish', vueObject.finish );
                    vueObject.setAttr( 'data-custom-desc', vueObject.custom_desc );
                    vueObject.setAttr( 'data-custom-sku', vueObject.custom_sku );

                    vueObject.setAttr( 'data-notes', vueObject.line_notes );

                    // If the species or the finish has changed, we will reset overrides
                    if ( vueObject.species != vueObject.original_species || vueObject.finish != vueObject.original_finish ) {
                        resetOverrides = true;
                    }

                    // Do processing that should only occur for custom or s4s
                    if ( vueObject.is_custom_or_s4s ) {
                        vueObject.setAttr( 'data-custom-thickness', vueObject.custom_thickness );
                        vueObject.setAttr( 'data-custom-width', vueObject.custom_width );

                        vueObject.setAttr( 'data-custom-thickness-friendly', jQuery( '#custom_thickness option[value="' + vueObject.custom_thickness + '"]' ).html().replace( '"', '' ) );
                        vueObject.setAttr( 'data-custom-width-friendly', jQuery( '#custom_width option[value="' + vueObject.custom_width + '"]' ).html().replace( '"', '' ) );

                        if ( !vueObject.original_ripsku.length || vueObject.custom_thickness != vueObject.original_thickness || vueObject.custom_width != vueObject.original_width || vueObject.species != vueObject.original_species ) {
                            resetOverrides = true;

                            var ourMaterial = jQuery( '#species option:selected' ).text();
                            if ( ourMaterial == 'Custom' ) {
                                ourMaterial = 'Poplar';
                            }
                
                            var params = {
                                thickness: parseFloat( vueObject.custom_thickness ),
                                width: parseFloat( vueObject.custom_width ),
                                material: ourMaterial,
                                quote_id: GSM.quote_id
                            }
                
                            updatingRipsku = true;
                            gsmAdminAjax( 'approximate_ripsku', params, function( response ) {
                                var decodedResponse = jQuery.parseJSON( response );

                                if ( decodedResponse.pricing_error ) {
                                    alert( 'No pricing is defined for this thickness - please call the mill for a quote' );

                                    vueObject.setAttr( 'data-custom-ripsku', '84RIP11.75' );
                                } else {
                                    vueObject.setAttr( 'data-custom-ripsku', decodedResponse.rip_sku );
                                }

                                vueObject.setAttr( 'data-custom-thicknesses', decodedResponse.selected_species.thicknesses.join( ',' ) );
        
                                jQuery( decodedResponse.selected_species.thicknesses ).each( function( i, x ) {
                                    vueObject.setAttr( 'data-custom-thickness-price-' + x, decodedResponse.selected_species.marked_up_yielded_costs[ x ] );
                                });

                                if ( resetOverrides ) {
                                    vueObject.resetOverrides();
                                }

                                vueObject.updateRowAndPricing();

                            //    alert( 'update pricing 4' );
                                gsUpdateTablePricing();
                            });
                        }
                    } else {
                        // This is a normal SKU
                    }

                    /* 
                    
                    Need Logic Here To Determine Messing With Overrides 

                    - If species is custom, force to override-final
                    - If first time, setup override defaults, second time, don't fuck with overrides unless species changes, custom thickness, custom width, or finish
                    - Only update RIPSKU if custom width / thickness has changed

                    */
                    
                    // We won't update the table if AJAX is running for the RIPSKU, it'll happen after
                    if ( !updatingRipsku ) {
                        if ( resetOverrides ) {
                            vueObject.resetOverrides();
                        }

                        vueObject.updateRowAndPricing();

                       // alert( 'update pricing 5' );
                        gsUpdateTablePricing();
                    }
                    
                    vueObject.modal.hide();
                });

                // This is the overrides box

                jQuery( '#cost_table tbody' ).on( 'click', 'button.overrides', function( e ) { 
                    e.preventDefault();

                    vueObject.parent_row = jQuery( this ).closest( 'tr' );
                    vueObject.setupCustomS4S();

                    vueObject.markup = vueObject.getAttrOrUseDefault( 'data-markup', 1.4 );
                    vueObject.gsm_adjust = vueObject.getAttrOrUseDefault( 'data-gsm-adjust', '1.00' );
                    vueObject.ripsku = vueObject.getAttrOrUseDefault( 'data-custom-ripsku', '' );
                    vueObject.custom_behaviour = vueObject.getAttrOrUseDefault( 'data-custom-behavior', 'default' );
                    vueObject.edge_glue = vueObject.getAttrOrUseDefault( 'data-edge-glue', '' );
                    vueObject.species = vueObject.getAttrOrUseDefault( 'data-species', 'poplar' );
                    vueObject.quantity = vueObject.parent_row.find( '.qty' ).val();
                    vueObject.unit_measure = vueObject.getAttrOrUseDefault( 'data-unit-measure', 'lf' );
                    vueObject.knife_charge = vueObject.getAttrOrUseDefault( 'data-knife-charge', 0 );
                    vueObject.edge_glue = vueObject.getAttrOrUseDefault( 'data-edge-glue', 0 );
                    vueObject.setup_charge_type = vueObject.getAttrOrUseDefault( 'data-setup-charge-type', 'normal' );
                    vueObject.include_setup_charge = vueObject.getAttrOrUseDefault( 'data-include-setup-charge', 0 );

                    if ( typeof GSM.config.primed_matrix != undefined ) {
                        if ( vueObject.is_custom_or_s4s ) {
                            vueObject.priming_cost_per_um = gsCalculatePrimingCost( vueObject.parent_row.attr( 'data-custom-width' ) );
                        } else {
                            vueObject.priming_cost_per_um = gsCalculatePrimingCost( vueObject.parent_row.attr( 'data-real-width' ) );
                        }
                    } else {
                        vueObject.priming_cost_per_um = GSM.config.primed_cost;
                    }
                
                // alert( vueObject.priming_cost_per_um );

                    vueObject.overrides_modal = new bootstrap.Modal( document.getElementById( 'overrides_modal' ));
                    vueObject.overrides_modal.show(); 

                    // Hide RIPSKU options I think?
                    var thicknesses = vueObject.getAttrOrUseDefault( 'data-custom-thicknesses', 0 );
                    if ( thicknesses ) {
                    // alert( jQuery( '#ripsku' ).html() );
                        jQuery( "#ripsku option" ).hide();

                        var actualThicknesses = thicknesses.split( ',' );
                        jQuery.each( actualThicknesses, function( i, x ){
                            jQuery( '#ripsku option.thick-' + x ).show();
                        });
                    }

                    // Clean up faulty items in the database that have fixed costs but shouldn't
                    vueObject.fixAttributes();


                //  alert( '1' + vueObject.knife_charge ) ;
                    // I'm guessing we have to compute the pricing here
                    vueObject.computeOverridePriceDefaults();
                    vueObject.updateOverridePricing();

                //  alert( '2' + vueObject.knife_charge ) ;
                });

                // Closing overrides box
                jQuery( '#overrides_modal .modal-footer button' ).on( 'click', function (e) {
                    e.preventDefault();

                    vueObject.setAttr( 'data-gsm-adjust', vueObject.gsm_adjust );
                    vueObject.setAttr( 'data-markup', vueObject.markup );
                    vueObject.setAttr( 'data-custom-ripsku', vueObject.ripsku );
                    vueObject.setAttr( 'data-custom-behavior', vueObject.custom_behaviour );
                    vueObject.setAttr( 'data-knife-charge', vueObject.knife_charge );
                    vueObject.setAttr( 'data-include-setup-charge', vueObject.include_setup_charge );
                    vueObject.setAttr( 'data-edge-glue', vueObject.edge_glue );

                    switch( vueObject.custom_behaviour ) {
                        case 'default':
                            vueObject.parent_row.removeAttr( 'data-override-finish' );
                            vueObject.parent_row.removeAttr( 'data-override-knife' );
                            vueObject.parent_row.removeAttr( 'data-override-setup' );
                            vueObject.parent_row.removeAttr( 'data-override-edge' );
                            vueObject.parent_row.removeAttr( 'data-override-charge' );
                            vueObject.parent_row.removeAttr( 'data-override-gsm' );

                            break;
                        case 'override-individual':
                            vueObject.setAttr( 'data-override-finish', vueObject.override_finish );
                            vueObject.setAttr( 'data-override-knife', vueObject.override_knife );
                            vueObject.setAttr( 'data-override-setup', vueObject.override_setup );
                            vueObject.setAttr( 'data-override-edge', vueObject.override_edge );

                            vueObject.parent_row.removeAttr( 'data-override-charge' );
                            vueObject.parent_row.removeAttr( 'data-override-gsm' );
        
                            break;
                        case 'override-final':
                            vueObject.parent_row.removeAttr( 'data-override-finish' );
                            vueObject.parent_row.removeAttr( 'data-override-knife' );
                            vueObject.parent_row.removeAttr( 'data-override-setup' );
                            vueObject.parent_row.removeAttr( 'data-override-edge' );

                            vueObject.setAttr( 'data-override-charge', parseFloat( vueObject.override_charge ).toFixed( 2 ) );

                            if ( vueObject.gsm_charge != vueObject.original_gsm_charge || vueObject.species == 'custom' ) {
                                vueObject.setAttr( 'data-override-gsm', parseFloat( vueObject.gsm_charge ).toFixed( 2 ) );
                            } else {
                                vueObject.parent_row.removeAttr( 'data-override-gsm' );
                            }
        
                            break;

                    }
                    // gsUpdateOverrideBehavior();
                    // have to figure out what happens here and redo it

                    vueObject.updateRowAndPricing();

                    vueObject.overrides_modal.hide(); 
                });
            }
        });    
    }

}

/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);


jQuery( document ).ready( function() { gsSetupVue(); } );
