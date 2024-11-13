<?php

namespace App\Controllers\WMS\NJ\Thornton;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'   => 'Thornton Dashboard',
        ];
        return view('wms/nj/thornton/index', $data);
    }
}
