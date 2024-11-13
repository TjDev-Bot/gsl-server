<?php
namespace Mouldings\Controllers;

use App\Controllers\BaseController;


class Mouldings extends BaseController {

    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('mouldings');
    }


    public function index() {
        // Write a function to query the database and get all the Mouldings in Codeigniter
        $data = [
            'title'   => 'Mouldings',
            'variable' => 'mouldings',
        ];
        return view('Mouldings\Views\mouldings\index', $data);
       
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
    
    public function all() {
        $query = $this->builder->get();
        $results = $query->getResult();
        $count = $query->getNumRows();
        if($results) {
            $data = [
            'count'    => $count,
            'data'      => $results,
        ];
        } else {
            $data = [
            'message'   => showDangerMessage("No records found."),
        ];
        }
        echo json_encode($data);
    }
    
    
    public function create() {

            $data = [
            'sku'              => $this->request->getPost('sku'),
            'thickness'              => $this->request->getPost('thickness'),
            'width'           => $this->request->getPost('width'),
            'profile'              => $this->request->getPost('profile'),
            'description'             => $this->request->getPost('description'),
            'mill_drawing'               => $this->request->getPost('mill_drawing'),
            'thumbnail_image'               => $this->request->getPost('photos'),
            'hdwd_ripsku'               => $this->request->getPost('hdwd_ripsku'),
            'hdwd_pieces'               => $this->request->getPost('hdwd_pieces'),
            'radiata_ripsku'               => $this->request->getPost('radiata_ripsku'),
            'radiata_pieces'               => $this->request->getPost('radiata_pieces'),
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
            'thickness'              => $this->request->getPost('thickness'),
            'width'           => $this->request->getPost('width'),
            'profile'              => $this->request->getPost('profile'),
            'description'             => $this->request->getPost('description'),
            'mill_drawing'               => $this->request->getPost('mill_drawing'),
            'thumbnail_image'               => $this->request->getPost('photos'),
            'hdwd_ripsku'               => $this->request->getPost('hdwd_ripsku'),
            'hdwd_pieces'               => $this->request->getPost('hdwd_pieces'),
            'radiata_ripsku'               => $this->request->getPost('radiata_ripsku'),
            'radiata_pieces'               => $this->request->getPost('radiata_pieces'),

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

