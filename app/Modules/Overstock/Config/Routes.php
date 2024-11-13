<?php
namespace Overstock\Config;

$routes->group('overstock', function($routes) {
    $routes->get('/',                   '\Overstock\Controllers\Overstock::index');
    $routes->get('read',                '\Overstock\Controllers\Overstock::read');
    $routes->post('create',             '\Overstock\Controllers\Overstock::create');
    $routes->post('edit',               '\Overstock\Controllers\Overstock::edit');
    $routes->post('update',             '\Overstock\Controllers\Overstock::update');
    $routes->post('delete',             '\Overstock\Controllers\Overstock::delete');
});