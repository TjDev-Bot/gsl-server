<?php
namespace Locations\Controllers;
use App\Controllers\BaseController;

class Location extends BaseController {

    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('locations'); 
       
    }

    public function index() {
        // Write a function to query the database and get all the locations in Codeigniter
        $data = [
            'title'   => 'Locations',
        ];
        return view('Locations\Views\index', $data);
       
    }

    public function read() {
        $query = $this->builder->get();
        $data = [
            'status'    => true,
            'locations'      => $query->getResult(),
        ];
        echo json_encode($data);
    }
    
    public function create() {

            $data = [
            'code'              => $this->request->getPost('code'),
            'name'              => $this->request->getPost('name'),
            'address'           => $this->request->getPost('address'),
            'city'              => $this->request->getPost('city'),
            'state'             => $this->request->getPost('state'),
            'zip'               => $this->request->getPost('zip'),
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
            'code'              => $this->request->getPost('code'),
            'name'              => $this->request->getPost('name'),
            'address'           => $this->request->getPost('address'),
            'city'              => $this->request->getPost('city'),
            'state'             => $this->request->getPost('state'),
            'zip'               => $this->request->getPost('zip'),

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
