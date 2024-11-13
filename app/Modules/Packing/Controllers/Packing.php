<?php
namespace Packing\Controllers;

use App\Controllers\BaseController;


class Packing extends BaseController {
    

    public function index() {

        return view('Packing\Views\packing-lists');
    }

  
    
}
