<?php

namespace Mouldings\Config;

$routes->group('mouldings', function($routes) {
    $routes->get('/',                   '\Mouldings\Controllers\Mouldings::index');
    $routes->get('read',                '\Mouldings\Controllers\Mouldings::read');
    $routes->get('all',                 '\Mouldings\Controllers\Mouldings::all');
    $routes->post('create',             '\Mouldings\Controllers\Mouldings::create');
    $routes->post('edit',     '\Mouldings\Controllers\Mouldings::edit');
    $routes->post('update',             '\Mouldings\Controllers\Mouldings::update');
    $routes->post('delete',             '\Mouldings\Controllers\Mouldings::delete');
});

$routes->group('profiles', function($routes) {
    $routes->get('/',                   '\Mouldings\Controllers\Profiles::index');
    $routes->get('read',                '\Mouldings\Controllers\Profiles::read');
    $routes->get('fetch',               '\Mouldings\Controllers\Profiles::fetch');
    $routes->get('all',                 '\Mouldings\Controllers\Profiles::all');
    $routes->post('create',             '\Mouldings\Controllers\Profiles::create');
    $routes->post('edit',               '\Mouldings\Controllers\Profiles::edit');
    $routes->post('update',             '\Mouldings\Controllers\Profiles::update');
    $routes->post('delete',             '\Mouldings\Controllers\Profiles::delete');
    $routes->post('fetch',              '\Mouldings\Controllers\Profiles::fetch');
    $routes->post('search',             '\Mouldings\Controllers\Profiles::search');
});

$routes->get('species', '\Mouldings\Controllers\Species::index', ['filter' => 'auth']);
