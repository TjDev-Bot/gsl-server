<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// No Login
$routes->group('', ['filter' => 'noauth'], function($routes) {
    $routes->get('login', 'Users\Login::login');
    $routes->get('msauth', 'Users\Login::msauth');
    $routes->get('mslogin', 'Users\Login::mslogin');
});

// Protected

$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('users/name', 'Users\Login::userName');
    
    $routes->get('wms', 'WMS\Dashboard::index');
    $routes->get('wms/(:segment)/(:segment)', 'WMS\BranchDashboard::index/$1/$2');
    $routes->get('wms/sc', 'WMS\SC\Dashboard::index');
    $routes->get('wms/(:segment)/delivery', 'WMS\Delivery::index/$1');
    $routes->get('wms/(:segment)/delivery/read', 'WMS\Delivery::read/$1');
    $routes->get('wms/(:segment)/delivery/delivery-dates', 'WMS\Delivery::deliveryDates/$1');
    $routes->post('wms/(:segment)/delivery/create', 'WMS\Delivery::create/$1');
    $routes->post('wms/(:segment)/delivery/edit', 'WMS\Delivery::edit/$1');
    $routes->post('wms/(:segment)/delivery/update', 'WMS\Delivery::update/$1');
    $routes->post('wms/(:segment)/delivery/delete', 'WMS\Delivery::delete/$1');
    $routes->post('wms/(:segment)/delivery/get-po', 'WMS\Delivery::getPo/$1');

    // Add other routes that require authentication and specific roles here

    $routes->get('logout', 'Users\Login::logout', ['filter' => 'auth']);
});
/*
// WMS Dashboard
$routes->group('wms', function($routes) {
    $routes->get('/', 'WMS\Dashboard::index', ['filter' => 'auth']);
      
      $routes->group('nj', function($routes) {
        $routes->get('/', 'WMS\NJ\Dashboard::index', ['filter' => 'auth']);
// receiving routes for WMS\NJ
$routes->group('delivery', function($routes) {
    // View all receiving
    $routes->get('/', 'WMS\NJ\Delivery::index', ['filter' => 'auth']);
    $routes->get('read', 'WMS\NJ\Delivery::read', ['filter' => 'auth']);
     $routes->get('delivery-dates', 'WMS\NJ\Delivery::deliveryDates', ['filter' => 'auth']);
    // Create a new receiving
    $routes->post('create', 'WMS\NJ\Delivery::create', ['filter' => 'auth']);
    // Edit a receiving
    $routes->post('edit', 'WMS\NJ\Delivery::edit', ['filter' => 'auth']);
    $routes->post('update', 'WMS\NJ\Delivery::update', ['filter' => 'auth']);
    // Delete a receiving
    $routes->post('delete', 'WMS\NJ\Delivery::delete', ['filter' => 'auth']);
    $routes->post('get-po', 'WMS\NJ\Delivery::getPo', ['filter' => 'auth']);

$routes->group('items', function($routes) {
    $routes->post('/', 'WMS\NJ\DeliveryItems::index', ['filter' => 'auth']);
    $routes->post('edit', 'WMS\NJ\DeliveryItems::edit', ['filter' => 'auth']);
    $routes->post('update', 'WMS\SC\DeliveryItems::update', ['filter' => 'auth']);
    $routes->post('delete', 'WMS\SC\DeliveryItems::delete', ['filter' => 'auth']);
    
});
});


// inventory routes for WMS\NJ
$routes->group('inventory', function($routes) {
    // View all inventory
    $routes->get('/', 'WMS\NJ\Inventory::index', ['filter' => 'auth']);
    $routes->get('read', 'WMS\NJ\Inventory::read', ['filter' => 'auth']);
    // Create a new inventory
    $routes->post('create', 'WMS\NJ\Inventory::create', ['filter' => 'auth']);
    // Edit a inventory
    $routes->post('edit', 'WMS\NJ\Inventory::edit', ['filter' => 'auth']);
    $routes->post('update', 'WMS\NJ\Inventory::update', ['filter' => 'auth']);
    // Delete a inventory
    $routes->post('delete', 'WMS\NJ\Inventory::delete', ['filter' => 'auth']);

});
});
 $routes->group('sc', function($routes) {
        $routes->get('/', 'WMS\SC\Dashboard::index', ['filter' => 'auth']);

  $routes->group('delivery', function($routes) {
    // View all receiving
    $routes->get('/', 'WMS\SC\Delivery::index', ['filter' => 'auth']);
    $routes->get('read', 'WMS\SC\Delivery::read', ['filter' => 'auth']);
     $routes->get('delivery-dates', 'WMS\SC\Delivery::deliveryDates', ['filter' => 'auth']);
    // Create a new receiving
    $routes->post('create', 'WMS\SC\Delivery::create', ['filter' => 'auth']);
    // Edit a receiving
    $routes->post('edit', 'WMS\SC\Delivery::edit', ['filter' => 'auth']);
    $routes->post('update', 'WMS\SC\Delivery::update', ['filter' => 'auth']);
    // Delete a receiving
    $routes->post('delete', 'WMS\SC\Delivery::delete', ['filter' => 'auth']);
    $routes->post('get-po', 'WMS\SC\Delivery::getPo', ['filter' => 'auth']);

    $routes->group('items', function($routes) {
        $routes->post('/', 'WMS\SC\DeliveryItems::index', ['filter' => 'auth']);
        $routes->post('edit', 'WMS\SC\DeliveryItems::edit', ['filter' => 'auth']);
        $routes->post('update', 'WMS\SC\DeliveryItems::update', ['filter' => 'auth']);
        $routes->post('delete', 'WMS\SC\DeliveryItems::delete', ['filter' => 'auth']);
        
    });
});   

     
     
 });
});

// WMS Thornton
// group routes for WMS Thornton
$routes->group('wms/nj/thornton', function($routes) {
     $routes->get('/', 'WMS\NJ\Thornton\Dashboard::index', ['filter' => 'auth']);
    // group routes for inventory
    $routes->group('inventory', function($routes) {
        // View all inventory
        $routes->get('/', 'WMS\NJ\Thornton\Inventory::index', ['filter' => 'auth']);
        $routes->get('read', 'WMS\NJ\Thornton\Inventory::read', ['filter' => 'auth']);
        // Create a new inventory
        $routes->post('create', 'WMS\NJ\Thornton\Inventory::create', ['filter' => 'auth']);
        // Edit a inventory
        $routes->post('edit', 'WMS\NJ\Thornton\Inventory::edit', ['filter' => 'auth']);
        $routes->post('update', 'WMS\NJ\Thornton\Inventory::update', ['filter' => 'auth']);
        // Delete a inventory
        $routes->post('delete', 'WMS\NJ\Thornton\Inventory::delete', ['filter' => 'auth']);
    });
    // group routes for receiving 
    $routes->group('receiving', function($routes) {
        // View all receiving
        $routes->get('/', 'WMS\NJ\Thornton\Receiving::index', ['filter' => 'auth']);
        $routes->get('read', 'WMS\NJ\Thornton\Receiving::read', ['filter' => 'auth']);
        $routes->get('delivery-dates', 'WMS\NJ\Thornton\Receiving::deliveryDates', ['filter' => 'auth']);
        // Create a new receiving
        $routes->post('create', 'WMS\NJ\Thornton\Receiving::create', ['filter' => 'auth']);
        // Edit a receiving
        $routes->post('edit', 'WMS\NJ\Thornton\Receiving::edit', ['filter' => 'auth']);
        $routes->post('update', 'WMS\NJ\Thornton\Receiving::update', ['filter' => 'auth']);
        // Delete a receiving
        $routes->post('delete', 'WMS\NJ\Thornton\Receiving::delete', ['filter' => 'auth']);
        $routes->post('process', 'WMS\NJ\Thornton\Receiving::process', ['filter' => 'auth']);
        $routes->post('start-process', 'WMS\NJ\Thornton\Receiving::startProcess', ['filter' => 'auth']);
        $routes->post('receive-item', 'WMS\NJ\Thornton\Receiving::receiveItem', ['filter' => 'auth']);
        $routes->post('inventory-sku', 'WMS\NJ\Thornton\Receiving::inventorySku', ['filter' => 'auth']);
        // Create units from the process-units ajax request
        $routes->post('create-units', 'WMS\NJ\Thornton\ReceivingUnits::createUnits', ['filter' => 'auth']);
    });
    
    
});

$routes->group('wms/nj/muller', function($routes) {
     $routes->get('/', 'WMS\NJ\Muller\Dashboard::index', ['filter' => 'auth']);
    // group routes for inventory
    $routes->group('inventory', function($routes) {
        // View all inventory
        $routes->get('/', 'WMS\NJ\Thornton\Inventory::index', ['filter' => 'auth']);
        $routes->get('read', 'WMS\NJ\Thornton\Inventory::read', ['filter' => 'auth']);
        // Create a new inventory
        $routes->post('create', 'WMS\NJ\Thornton\Inventory::create', ['filter' => 'auth']);
        // Edit a inventory
        $routes->post('edit', 'WMS\NJ\Thornton\Inventory::edit', ['filter' => 'auth']);
        $routes->post('update', 'WMS\NJ\Thornton\Inventory::update', ['filter' => 'auth']);
        // Delete a inventory
        $routes->post('delete', 'WMS\NJ\Thornton\Inventory::delete', ['filter' => 'auth']);
    });
    // group routes for receiving 
    $routes->group('receiving', function($routes) {
        // View all receiving
        $routes->get('/', 'WMS\NJ\Muller\Receiving::index', ['filter' => 'auth']);
        $routes->get('read', 'WMS\NJ\Muller\Receiving::read', ['filter' => 'auth']);
         $routes->get('delivery-dates', 'WMS\NJ\Muller\Receiving::deliveryDates', ['filter' => 'auth']);
        // Create a new receiving
        $routes->post('create', 'WMS\NJ\Muller\Receiving::create', ['filter' => 'auth']);
        // Edit a receiving
        $routes->post('edit', 'WMS\NJ\Muller\Receiving::edit', ['filter' => 'auth']);
        $routes->post('update', 'WMS\NJ\Muller\Receiving::update', ['filter' => 'auth']);
        // Delete a receiving
        $routes->post('delete', 'WMS\NJ\Muller\Receiving::delete', ['filter' => 'auth']);
        $routes->post('get-po', 'WMS\NJ\Muller\Receiving::getPo', ['filter' => 'auth']);
        $routes->post('process', 'WMS\NJ\Muller\Receiving::process', ['filter' => 'auth']);
        $routes->post('start-process', 'WMS\NJ\Muller\Receiving::startProcess', ['filter' => 'auth']);
        $routes->post('receive-item', 'WMS\NJ\Muller\Receiving::receiveItem', ['filter' => 'auth']);
        $routes->post('inventory-sku', 'WMS\NJ\Muller\Receiving::inventorySku', ['filter' => 'auth']);
    });
    
});

*/