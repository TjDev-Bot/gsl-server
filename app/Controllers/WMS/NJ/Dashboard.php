<?php

namespace App\Controllers\WMS\NJ;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'   => 'NJ WMS',
        ];
        return view('wms/nj/index', $data);
    }
}
