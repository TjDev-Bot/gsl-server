<?php

namespace App\Controllers\WMS\NJ\Muller;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'   => 'Muller Dashboard',
        ];
        return view('wms/nj/muller/index', $data);
    }
}
