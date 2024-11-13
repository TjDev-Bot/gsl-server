<?php
namespace Shipments\Controllers;

use App\Controllers\BaseController;


class Shipments extends BaseController {
    

    public function index() {

        return view('Shipments\Views\shipments');
    }

  
    
}
