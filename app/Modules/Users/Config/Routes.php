<?php

namespace Users\Config;


$routes->get('logout', '\Users\Controllers\Login::logout', ['filter' => 'auth']);

/* Email */
$routes->get('login', '\Users\Controllers\Login::login');

/* Microsoft */
$routes->get('msauth', '\Users\Controllers\Login::msauth', ['filter' => 'noauth']);
$routes->get('mslogin', '\Users\Controllers\Login::mslogin', ['filter' => 'noauth']);

/* Google */
$routes->get('gauth', 'Users\Controllers\GLogin::gauth', ['filter' => 'noauth']);
$routes->get('glogin', 'Users\Controllers\GLogin::glogin', ['filter' => 'noauth']);

$routes->add('create', 'Users\Controllers\Users::create', ['filter' => 'auth']);
$routes->add('profile', 'Users\Controllers\Profile::profile', ['filter' => 'auth']);