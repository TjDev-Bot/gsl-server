<?php
namespace App\Controllers\WMS\NJ\Locations\Thornton;
use App\Controllers\BaseController;

class Inventory extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('thornton_inventory');
    }


    public function index() {
        // Write a function to query the database and get all the Mouldings in Codeigniter
        $data = [
            'title'   => 'Thornton Inventory',
            'route'   => 'wms/nj/thornton/inventory',
        ];
        return view('wms/nj/thornton/inventory/index', $data);
       
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


    public function update() {
        $data = [
            'id'              => $this->request->getPost('id'),
            'unit_id'              => $this->request->getPost('unit_id'),
            'sku'              => $this->request->getPost('sku'),
            'po'             => $this->request->getPost('po'),
            'unit_no'              => $this->request->getPost('unit_no'),
            'vendor'               => $this->request->getPost('supplier'),
            'qty'              => $this->request->getPost('qty'),
            'length'              => $this->request->getPost('length'),
            'total'               => $this->request->getPost('total'),
            'damage'              => $this->request->getPost('damage'),
            'date_in'               => $this->request->getPost('date_in'),
            'date_out'              => $this->request->getPost('date_out'),
            'customer'               => $this->request->getPost('customer'),
            'notes'               => $this->request->getPost('notes')

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