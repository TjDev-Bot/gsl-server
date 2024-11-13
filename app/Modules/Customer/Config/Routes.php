<?php

namespace Customer\Config;

// Customer Routes
$routes->group('customer', function($routes) {
    $routes->get('/',                   '\Customer\Controllers\Customer::index');
    $routes->get('list',                '\Customer\Controllers\Customer::list');
    $routes->post('datatable',          '\Customer\Controllers\Customer::datatable');
    $routes->post('create',             '\Customer\Controllers\Customer::create');
    $routes->post('edit',               '\Customer\Controllers\Customer::edit');
    $routes->post('update',             '\Customer\Controllers\Customer::update');
    $routes->post('delete',             '\Customer\Controllers\Customer::delete');
});
