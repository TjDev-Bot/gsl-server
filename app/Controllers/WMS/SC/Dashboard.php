<?php

namespace App\Controllers\WMS\SC;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'   => 'SC WMS',
        ];
        return view('wms/sc/index', $data);
    }
}
