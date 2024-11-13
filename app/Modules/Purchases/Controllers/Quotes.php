<?php
namespace Purchases\Controllers;

use App\Controllers\BaseController;


class Quotes extends BaseController {
    

    public function index() {

        return view('Purchases\Views\quotes');
    }

  
    
}
