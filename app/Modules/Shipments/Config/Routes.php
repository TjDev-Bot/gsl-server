<?php

namespace Shipments\Config;

$routes->get('shipments', '\Shipments\Controllers\Shipments::index', ['filter' => 'auth']);

