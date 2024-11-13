<?php
namespace App\Controllers\WMS\NJ;
use App\Controllers\BaseController;

class Receiving extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('nj_receiving');
    }


    public function index() {
        // Write a function to query the database and get all the Mouldings in Codeigniter
        $data = [
            'title'   => 'NJ Receiving',
            'route'   => 'wms/nj/receiving',
        ];
        return view('wms/nj/receiving/index', $data);
       
    }

    public function read() {
        $query = $this->builder->get();
        $results = $query->getResult();
        echo json_encode($results);
    }
    public function receiveDates() {
        // get the start and end dates in the ISO format from the request and query the thornton_receiving table for the dates range using the scheuled field. Return the po_no, supplier_code and scheduled fields & return the each scheduled date in a format that fullcalendar can render. Let the title be the po_no - supplier_code
    
        $query = $this->db->table('nj_receiving')->select('po_no, supplier_code, delivery_date, status, color, text_color')->get();
        $results = $query->getResultArray();
        $data = [];
        foreach ($results as $result) {
            $data[] = array(
                'title' => $result['po_no'] . ' - ' . $result['supplier_code'],
                'start' => $result['delivery_date'],
                'end' => $result['delivery_date'],
                'color' => $result['color'],
                'textColor' => $result['text_color'],
                'status' => $result['status']
            );
        }
        echo json_encode($data);       
    }


    public function getPo() {
    $po_no =  $this->request->getPost('po_no');
    $builder = $this->db->table('po_open')->getWhere(['po_no' => $po_no]);
    $po_header  = $builder->getRowArray();
    $query = $this->db->table('po_open_items')->select('sku')->getWhere(['po_no'=> $po_no]);
   // grab the items and create a comma separated array of items
    $results = $query->getResultArray();
    $po_header['items'] = $results;
    if($po_header) {
        $json = [
        'status'    => true,
        'data'      => $po_header,

    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => showDangerMessage("No records found."),
    ];
    }
    echo json_encode($json);
}

public function create() {
    $data = [
    'po_no'             => $this->request->getPost('po_no'),
    'verbal_po'         => $this->request->getPost('verbal_po'),
    'branch_code'       => $this->request->getPost('branch_code'),
    'supplier_code'     => $this->request->getPost('supplier_code'),
    'reference'       => $this->request->getPost('reference'),
    'description'       => $this->request->getPost('description'),
    'location'  => $this->request->getPost('location'),
    'booked_by'              => $this->request->getPost('user'),
    'delivery_date'               => $this->request->getPost('delivery_date'),
    'po_items'               => $this->request->getPost('po_items'),
    'notes'               => $this->request->getPost('notes')
];

$result = $this->builder->insert($data);
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


public function edit() {
    $po_no = $this->request->getPost('po_no');
    $query = $this->builder->getWhere(['po_no' => $po_no]);
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


public function update() {
$data = [
    'id'             => $this->request->getPost('id'),
    'po_no'             => $this->request->getPost('po_no'),
    'verbal_po'         => $this->request->getPost('verbal_po'),
    'branch_code'       => $this->request->getPost('branch_code'),
    'supplier_code'     => $this->request->getPost('supplier_code'),
    'reference'       => $this->request->getPost('reference'),
    'description' => $this->request->getPost('description'),
    'location'  => $this->request->getPost('location'),
    'booked_by'              => $this->request->getPost('user'),
    'delivery_date'               => $this->request->getPost('delivery_date'),
    'po_items'               => $this->request->getPost('po_items'),
    'notes'               => $this->request->getPost('notes')

];
// update where the po_no and id are the same as the po_no in the request
$where = ['po_no' => $data['po_no'], 'id' => $data['id']];
$result = $this->builder->update($data, $where);
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

public function delete() {
    $data = [
    'po_no'             => $this->request->getPost('po_no'),
    'id'             => $this->request->getPost('id'),
];
$where = ['po_no' => $data['po_no'], 'id' => $data['id']];
$result = $this->builder->delete($where);
// delete all the items where the po_no is the same as the po_no in the request from the nj_receiving_items table
/* 
$this->db->table('nj_receiving_items')->delete(['po_no' => $data['po_no']]);
*/
    if($result) {
        $json = [
                'status'    => true,
        ];
    } else {
        $json = [
                'status'    => false,
    ];
    }
    echo json_encode($json);
}

public function process() {
    $po_no =  $this->request->getPost('po_no');
    $query = $this->db->table('po_open_items')->select('*')->getWhere(['po_no'=> $po_no]);
   // $query = $this->db->table('nj_receiving_items')->select('*')->getWhere(['po_no'=> $po_no]);
    $results = $query->getResultArray();
    foreach ($results as $result) {
        $items[] = array(
     'po_no' => $result['po_no'],
            'sku' => $result['sku'],
            'status' => 'OPEN',
            'description' => $result['description'],
            'supplier_code' => $result['supplier_code'],
            'length' => $result['length'],
            'uom' => $result['uom'],
            'total_pcs' => $result['total_pcs'],
            'total_qty' => $result['total_qty']
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


// Update the nj_receiving table with the status, unit_qty and total_units with all the items submitted in the receiving items post
public function receivingItems() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'id'            => $item['id'],
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
    $this->db->table('nj_receiving_items')->upsertBatch($data);
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

// Get all the items from the nj_receiving_items table where the po_no is the same as the po_no in the request
public function getReceivingItems() {
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
            'total_qty' => $item['total_qty'],
            'total_uom' => $item['total_uom'],
            'damage' => $item['damage'],
            'notes'  => $item['notes']
        );
    }
    // count the number of items in the array
    $count = count($data);
    // insert or update the inventory table
    $this->db->table('nj_inventory')->upsertBatch($data);
    

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

function inventorySku() {
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