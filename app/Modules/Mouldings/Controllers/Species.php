<?php
namespace Mouldings\Controllers;

use App\Controllers\BaseController;


class Species extends BaseController {
    
        public function index()
    {
    $db      = \Config\Database::connect('default');
    $builder = $db->table('species');
    $query   = $builder->get();

$data = [
    'results' => $query->getResult(),
];

return view('Mouldings\Views\species', $data);

    }

  
    
}