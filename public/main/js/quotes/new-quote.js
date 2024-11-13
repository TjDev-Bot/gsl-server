
//*************** */
// CUSTOMER SECTION
//*************** */
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


    doGsOrderTabs();


//*************** */
// SHIPMENT SECTION
//*************** */

function isTaxable() {
    var taxAmount = jQuery( '#tax_rate' ).val();
    return( taxAmount > 0.0001 );
}

function getTaxRate() {
    var taxValue = jQuery( '#tax_rate' ).val();
    return parseFloat( taxValue );  
}

function getGlobalMarkup() {
    var globalMarkup = jQuery( '#master_markup' ).val();

    return globalMarkup;
}


// ***********************************
    // LINE ITEM FUNCTIONS
    // ***********************************

    function enableConfigureButton( element ) {
        element.find( 'button.options' ).removeAttr( 'disabled' );
    }
    
    function enableOverrideButton( element ) {
        element.find( 'button.overrides' ).removeAttr( 'disabled' );
    }


    function addRowToTable() {
        var dummyData = jQuery( 'tr.dummy' ).html();
        jQuery( '#cost_table tbody' ).append( '<tr data-markup="60" class="new-item" data-unit-measure="lf" data-gsm-adjust="1.00" data-is-active="1">' + dummyData + '</tr>' );
        jQuery( 'tr.new-item' ).find( 'btn.overrides' ).prop( 'disabled', true ).removeClass( 'new-item' );
    
        gsRenumberRows();
    }
    jQuery( '#add_new_item' ).click( function( e ) {
        gsAddRowToTable();
        e.preventDefault();
    });


    var initialRows = jQuery( '#cost_table tbody tr' );
    if ( initialRows.length == 1 ) {
        gsAddRowToTable();
    }


