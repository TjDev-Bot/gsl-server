<?php

namespace Packing\Config;

$routes->get('packing-lists', '\Packing\Controllers\Packing::index', ['filter' => 'auth']);

