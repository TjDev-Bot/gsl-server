<?php

namespace App\Controllers\WMS;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'WMS Main Dashboard',
        ];
        return view('wms/index', $data);
    }
}