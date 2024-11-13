<?php
namespace App\Controllers\WMS\NJ;
use App\Controllers\BaseController;

class DeliveryItems extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('receiving_items_nj');
    }

    public function index() {
        $po_no =  $this->request->getPost('po_no');
        $builder = $this->db->table('receiving_nj')->select('po_no, supplier_code, supplier_id, status')->getWhere(['po_no' => $po_no]);
        $po_header = $builder->getRowArray(); 
        $query = $this->db->table('po_stock_items_nj')->select('*')->getWhere(['po_no'=> $po_no]);
        $results = $query->getResultArray();
            foreach ($results as $result) {
                $items[] = array(
                    'stock_id' => $result['stock_id'],
                    'sku' => $result['sku'],
                    'description' => $result['description'],
                    'length' => $result['length'],
                    'status' => $result['status'],
                    'order_qty' => $result['order_qty'],
                    'order_uom' => $result['order_uom'],
                    'stock_qty' => $result['stock_qty'],
                    'stock_uom' => $result['stock_uom'],
                    'unit_qty' => $result['unit_qty'],
                    'unit_uom' => $result['unit_uom'],
                    'total_units' => $result['total_units'],
                );
            }
        $po_header['items'] = $items;
        if($po_header) {
            $json = [
            'status'    => true,
            'data'      => $po_header,
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
public function createItems() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'po_no'         => $item['po_no'],
            'sku'           => $item['sku'],
            'status'        => $item['status'],
            'description'   => $item['description'],
            'supplier_code' => $item['supplier_code'],
            'length'        => $item['length'],
            'total_pcs'     => $item['total_pcs'],
            'total_qty'     => $item['total_qty'],
            'total_uom'     => $item['total_uom'],
            'total_units'   => $item['total_units'],
            'unit_qty'      => $item['unit_qty'],
            'unit_uom'      => $item['unit_uom']
        );
    }
// update batch based on the id and the po_no
    $this->db->table('nj_receiving_items')->insertBatch($data);
    // update the nj_receiving table with the  receiving status from the request where the po_no is the same as the po_no in the items array. Set the color to #FFA500 and the text_color to black
    $this->db->table('nj_receiving')->update(['status' => $data[0]['status'], 'color' => '#FFA500', 'text_color' => 'black'], ['po_no' => $data[0]['po_no']]);
    if($data) {
        $json = [
        'status'    => true,
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => "Something went wrong. Please try again!",
    ];
    }
    echo json_encode($json);
}
    
// Get all the items from the nj_receiving_items table where the po_no is the same as the po_no in the request
public function editItems() {
    $po_no = $this->request->getPost('po_no');
    $query = $this->db->table('nj_receiving_items')->select('*')->getWhere(['po_no'=> $po_no]);
    $results = $query->getResultArray();
    foreach ($results as $result) {
        $items[] = array(
            'id' => $result['id'],
            'po_no' => $result['po_no'],
            'sku' => $result['sku'],
            'status' => $result['status'],
            'description' => $result['description'],
            'supplier_code' => $result['supplier_code'],
            'length' => $result['length'],
            'total_pcs' => $result['total_pcs'],
            'total_qty' => $result['total_qty'],
            'total_uom' => $result['total_uom'],
            'total_units' => $result['total_units'],
            'unit_qty' => $result['unit_qty'],
            'unit_uom' => $result['unit_uom']
        );
    }
    $data['items'] = $items;
    if($results) {
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

// Update the nj_receiving_items table with the the total_units & unit qty only on the ids where there is a change. Use a transaction to update update each individually clearing any binds

public function updateItems() {
    $items = $this->request->getPost('items');
    $po_no = $this->request->getPost('po_no');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'id'            => $item['id'],
            'total_pcs'     => $item['total_pcs'],
            'total_qty'     => $item['total_qty'],
            'total_uom'     => $item['total_uom'],
            'total_units'   => $item['total_units'],
            'unit_qty'      => $item['unit_qty'],
            'unit_uom'      => $item['unit_uom']
        );
    }
    $this->db->transStart();
    foreach ($data as $item) {
        $this->db->table('nj_receiving_items')->update($item, ['id' => $item['id']]);
    }
    $this->db->transComplete();
    // clear the binds and close the database connection
    $this->db->close();

    if ($this->db->transStatus() === false) {

        $json = [
            'status'    => false,
            'message'   => "Something went wrong. Please try again!",
        ];
    } else {
        // query the nj_receiving_items table and get the updated items and send them back as a response

    }
    echo json_encode($json);
}
    /*
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'id'            => $item['id'],
          //  'po_no'         => $item['po_no'],
          //  'sku'           => $item['sku'],
           // 'status'        => $item['status'],
         //   'description'   => $item['description'],
        //    'supplier_code' => $item['supplier_code'],
       //     'length'        => $item['length'],
        //    'total_pcs'     => $item['total_pcs'],
       //     'total_qty'     => $item['total_qty'],
       //     'total_uom'     => $item['total_uom'],
            'total_units'   => $item['total_units'],
            'unit_qty'      => $item['unit_qty'],
    //        'unit_uom'      => $item['unit_uom']
        );
    }
    $this->db->table('nj_receiving_items')->updateBatch($data, 'id');
    // query the nj_receiving_items table and get the updated items and send them back as a response
    $query = $this->db->table('nj_receiving_items')->select('*')->getWhere(['po_no'=> $data[0]['po_no']]);
    $results = $query->getResultArray();
    foreach ($results as $result) {
        $items[] = array(
            'id' => $result['id'],
            'po_no' => $result['po_no'],
            'sku' => $result['sku'],
            'status' => $result['status'],
            'description' => $result['description'],
            'supplier_code' => $result['supplier_code'],
            'length' => $result['length'],
            'total_pcs' => $result['total_pcs'],
            'total_qty' => $result['total_qty'],
            'total_uom' => $result['total_uom'],
            'total_units' => $result['total_units'],
            'unit_qty' => $result['unit_qty'],
            'unit_uom' => $result['unit_uom']
        );
    }
    $data['items'] = $items;
if($results) {
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
*/




// Grab all the items from the process-item response and insert each item into the thornton_inventory table
public function createUnits() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'unit_id' => $item['unit_id'],
            'sku' => $item['sku'],
            'status' => $item['status'],
            'po_no' => $item['po_no'],
            'supplier_code' => $item['supplier_code'],
            'unit_qty' => $item['unit_qty'],
            'unit_uom' => $item['unit_uom'],
            'length' => $item['length'],
            'total_qty' => $item['total_qty'],
            'total_uom' => $item['total_uom'],
            'damage' => $item['damage'],
            'notes'  => $item['notes']
        );
    }
    // count the number of items in the array
    $count = count($data);
    // insert or update the inventory table
    $this->db->table('nj_inventory')->insertBatch($data);
    

    if($data) {
        $json = [
        'status'    => true,
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => "Something went wrong. Please try again!",
    ];

    
}
echo json_encode($json);
}

// Update units in the nj_inventory table using a transaction 
public function updateUnits() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'id' => $item['id'],
            'unit_id' => $item['unit_id'],
            'sku' => $item['sku'],
            'status' => $item['status'],
            'po_no' => $item['po_no'],
            'supplier_code' => $item['supplier_code'],
            'unit_qty' => $item['unit_qty'],
            'unit_uom' => $item['unit_uom'],
            'length' => $item['length'],
            'total_qty' => $item['total_qty'],
            'total_uom' => $item['total_uom'],
            'damage' => $item['damage'],
            'notes'  => $item['notes']
        );
    }
    $this->db->table('nj_inventory')->updateBatch($data, 'unit_id');
    if($data) {
        $json = [
        'status'    => true,
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => "Something went wrong. Please try again!",
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
                'message'   => "Something went wrong. Please try again!",
    ];
    }
    echo json_encode($json);
}


public function inventorySku() {
    // grab the sku from the request and query the thornton_inventory table
    $sku = $this->request->getPost('sku');
    $query = $this->db->table('nj_inventory')->getWhere(['sku' => $sku]);
    $results = $query->getResultArray();
    if($results) {
        $json = [
        'status'    => true,
        'data'      => $results,
    ];
    } else {
        $json = [
        'status'    => false,
        'message'   => showDangerMessage("No records found."),
    ];
    }
    echo json_encode($json);

}


}