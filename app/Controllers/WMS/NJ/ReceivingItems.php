<?php
namespace App\Controllers\WMS\NJ;
use App\Controllers\BaseController;

class ReceivingItems extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('nj_receiving_items');
    }


    public function index() {
        $po_no =  $this->request->getPost('po_no');
        $query = $this->db->table('po_open_items')->select('*')->getWhere(['po_no'=> $po_no]);
        $results = $query->getResultArray();
            foreach ($results as $result) {
                $items[] = array(
                    'po_no'         => $result['po_no'],
                    'sku'           => $result['sku'],
                    'status'        => 'OPEN',
                    'description'   => $result['description'],
                    'supplier_code' => $result['supplier_code'],
                    'length'        => $result['length'],
                    'uom'           => $result['uom'],
                    'total_pcs'     => $result['total_pcs'],
                    'total_qty'     => $result['total_qty']
                );
            }
        $data['items'] = $items;
            if ($results) {
                $json = [
                    'status'    => true,
                    'data'      => $data,
                        ];
            } else {
                $json = [
                'status'    => false,
                'message'   => "No records found.",
                 ]; 
                    }
            echo json_encode($json);
       
        }


// Update the nj_receiving table with the status, unit_qty and total_units with all the items submitted in the receiving items post
public function receivingItems() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'id' => $item['id'],
            'po_no' => $item['po_no'],
            'sku' => $item['sku'],
            'status' => $item['status'],
            'unit_qty' => $item['unit_qty'],
            'total_units' => $item['total_units'],
        );
    }
// update batch based on the id and the po_no
    $this->db->table('nj_receiving_items')->updateBatch($data, 'id');
    // update the nj_receiving table with the  receiving status from the request where the po_no is the same as the po_no in the items array. Set the color to #FFA500 and the text_color to black
    $this->db->table('nj_receiving')->update(['status' => $data[0]['status'], 'color' => '#FFA500', 'text_color' => 'black'], ['po_no' => $data[0]['po_no']]);
    if($data) {
        $json = [
        'status'    => true,
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => showDangerMessage("Something went wrong. Please try again!"),
    ];
    }
    echo json_encode($json);
}

// Grab all the items from the process-item response and insert each item into the thornton_inventory table
public function receiveItems() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'unit_id' => $item['unit_id'],
            'sku' => $item['sku'],
            'po_no' => $item['po_no'],
            'supplier_code' => $item['supplier_code'],
            'unit_qty' => $item['unit_qty'],
            'unit_uom' => $item['unit_uom'],
            'length' => $item['length'],
            'total' => $item['total_unit_qty'],
            'total_uom' => $item['total_uom'],
            'damage' => $item['damage'],
          
        );
    }
    // count the number of items in the array
    $count = count($data);
    // insert or update the inventory table
    $this->db->table('nj_inventory')->insertBatch($data);
    // update the thornton_receiving_items table with the unit_qty and total_units where the total units is the count of the items just received
    $this->db->table('nj_receiving_items')->update(['unit_qty' => $data[0]['unit_qty'], 'total_units' => $count], ['po_no' => $data[0]['po_no']]);
    

    if($data) {
        $json = [
        'status'    => true,
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => showDangerMessage("Something went wrong. Please try again!"),
    ];

    
}
echo json_encode($json);
}

// Delete sku from the nj_receiving_items table from the id request
public function deletePoItem() {
    $data = [
    'id'             => $this->request->getPost('id'),
    'po_no'             => $this->request->getPost('po_no'),
    'sku'             => $this->request->getPost('sku'),
];
    $where = ['id' => $data['id'], 'po_no' => $data['po_no'], 'sku' => $data['sku']];
    $result = $this->db->table('nj_receiving_items')->delete($where);
    $query = $this->db->table('nj_receiving')->select('po_items')->getWhere(['po_no'=> $data['po_no']]);
    $results = $query->getRowArray();
    $po_items = explode(',', $results['po_items']);
    $key = array_search($data['sku'], $po_items);
    unset($po_items[$key]);
    $po_items = implode(',', $po_items);
    $this->db->table('nj_receiving')->update(['po_items' => $po_items], ['po_no' => $data['po_no']]);
    if($result) {
        $json = [
        'status'    => true,
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