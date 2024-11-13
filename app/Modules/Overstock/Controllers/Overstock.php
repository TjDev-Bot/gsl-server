<?php
namespace Overstock\Controllers;
use App\Controllers\BaseController;

class Overstock extends BaseController {

    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('overstock'); 
       
    }

    public function index() {
        // Write a function to query the database and get all the Overstock in Codeigniter
        $data = [
            'title'   => 'Overstock',
        ];
        return view('Overstock\Views\index', $data);
       
    }

    // Write a function to query the database for the filters selected in the search form
    public function search() {
        $material = $this->request->getPost('material');
        $length = $this->request->getPost('length');
        $um = $this->request->getPost('um');
        $query = $this->builder->getWhere(['material'=> $material, 'length' => $length, 'um' => $um]);
        $results = $query->getResult();
        echo json_encode($results);
    }

    public function read() {
        $query = $this->builder->get();
        $results = $query->getResult();
        echo json_encode($results);
    }
    
    public function create() {

            $data = [
            'sku'              => $this->request->getPost('sku'),
            'qty'              => $this->request->getPost('qty'),
            'um'           => $this->request->getPost('um'),
            'length'              => $this->request->getPost('length'),
            'description'             => $this->request->getPost('description'),
            'price'               => $this->request->getPost('price'),
            'photos'               => $this->request->getPost('photos'),
        ];

       $result = $this->builder->insert($data);
            if($result) {
                $json = [
                'status'    => true,
                        'message'   => showSuccessMessage("New record has been created successfully"),
            ];
            } else {
                $json = [
                'status'    => false,
                        'message'   => showDangerMessage("Something went wrong. Please try again!"),
            ];
        }
        echo json_encode($json);
    }

    public function update() {
        $data = [
            'id'              => $this->request->getPost('id'),
            'sku'              => $this->request->getPost('sku'),
            'qty'              => $this->request->getPost('qty'),
            'um'           => $this->request->getPost('um'),
            'length'              => $this->request->getPost('length'),
            'description'             => $this->request->getPost('description'),
            'price'               => $this->request->getPost('price'),
            'photos'               => $this->request->getPost('photos'),

        ];
        $result = $this->builder->update($data, ['id' => $data['id']]); 
            if($result) {
                $json = [
                'status'    => true,
                        'message'   => showSuccessMessage("Selected record has been updated successfully"),
            ];
            } else {
                $json = [
                'status'    => false,
                        'message'   => showDangerMessage("Something went wrong. Please try again!"),
            ];
            }
        echo json_encode($json);
    }

    public function delete() {
        $result = $this->builder->delete(['id' => $this->request->getPost('id')]);

        if($result) {
            $json = [
            'message'   => showSuccessMessage("The selected record has been deleted successfully."),
                    'status'    => true,
            ];
        } else {
            $json = [
            'message'   => showDangerMessage("Something went wrong. Please try again."),
                    'status'    => false,
        ];
        }
        echo json_encode($json);
    }

    public function edit() {
        $id = $this->request->getPost('id');
        $query = $this->builder->getWhere(['id' => $id]);
        $result = $query->getRow();
        if($result) {
            $json = [
            'status'    => true,
                    'data'      => $result,
        ];
        } else {
            $json = [
            'status'    => false,
                    'message'   => showDangerMessage("Something went wrong. Please try again!"),
        ];
        }
        echo json_encode($json);
    }

 
}
