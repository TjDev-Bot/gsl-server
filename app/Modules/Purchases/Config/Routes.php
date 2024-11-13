<?php

namespace Purchases\Config;

$routes->get('orders', '\Purchases\Controllers\Orders::index', ['filter' => 'auth']);

