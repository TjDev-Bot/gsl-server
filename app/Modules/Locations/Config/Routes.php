<?php

namespace Locations\Config;

$routes->group('locations', function($routes) {
    $routes->get('/',                   '\Locations\Controllers\Location::index');
    $routes->get('read',                '\Locations\Controllers\Location::read');
    $routes->post('create',             '\Locations\Controllers\Location::create');
    $routes->post('edit',               '\Locations\Controllers\Location::edit');
    $routes->post('update',             '\Locations\Controllers\Location::update');
    $routes->post('delete',             '\Locations\Controllers\Location::delete');
});