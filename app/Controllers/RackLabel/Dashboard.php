<?php
namespace App\Controllers\RackLabel;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
   /* public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('nj_receiving');
    }

*/
    public function index() {
        // Write a function to query the database and get all the Mouldings in Codeigniter
        $data = [
            'title'   => 'Rack Label Dashboard',
           // 'route'   => 'wms/nj/receiving',
        ];
        return view('RackLabel/index', $data);
       
    }

    public function read() {
        $query = $this->builder->get();
        $results = $query->getResult();
        echo json_encode($results);
    }
    public function receiveDates() {
        // get the start and end dates in the ISO format from the request and query the thornton_receiving table for the dates range using the scheuled field. Return the po_no, supplier_code and scheduled fields & return the each scheduled date in a format that fullcalendar can render. Let the title be the po_no - supplier_code
    
        $query = $this->db->table('nj_receiving')->select('po_no, supplier_code, scheduled, status')->get();
        $results = $query->getResultArray();
        $data = [];
        foreach ($results as $result) {
            $data[] = array(
                'title' => $result['supplier_code'] . ' ' . $result['po_no'],
                'delivery' => $result['scheduled'],
                'status' => $result['status'],
            );
        }
        if ($data) {
            $json = [
                'status'    => true,
                'items'      => $data,
            ];
        } else {
            $json = [
                'status'    => false,
                'message'   => showDangerMessage("No records found."),
            ];
        }
        echo json_encode($json);
    

        
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
    'description'       => $this->request->getPost('description'),
    'reference'       => $this->request->getPost('reference'),
    'location'  => $this->request->getPost('location'),
    'booked_by'              => $this->request->getPost('booked_by'),
    'scheduled'               => $this->request->getPost('scheduled'),
    'status'               => $this->request->getPost('status'),
    'po_items'               => $this->request->getPost('po_items'),
    'notes'               => $this->request->getPost('notes')
];

$result = $this->builder->insert($data);
$query = $this->db->table('po_open_items')->select('*')->getWhere(['po_no'=> $data['po_no']]);
// grab results and insert batch into thornton_receiving_items
 $results = $query->getResultArray();
    $data = [];
    foreach ($results as $result) {
        $data[] = array(
            'po_line_no' => $result['po_line_no'],
            'sku' => $result['sku'],
            'description' => $result['description'],
            'po_no' => $result['po_no'],
            'supplier_code' => $result['supplier_code'],
            'length' => $result['length'],
            'uom' => $result['uom'],
            'total_pcs' => $result['total_pcs'],
            'total_qty' => $result['total_qty']
        );
    }
    $this->db->table('nj_receiving_items')->insertBatch($data);
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
    'po_no'             => $this->request->getPost('po_no'),
    'verbal_po'         => $this->request->getPost('verbal_po'),
    'branch_code'       => $this->request->getPost('branch_code'),
    'supplier_code'     => $this->request->getPost('supplier_code'),
    'description'       => $this->request->getPost('description'),
    'reference'       => $this->request->getPost('reference'),
    'location'  => $this->request->getPost('location'),
    'booked_by'              => $this->request->getPost('booked_by'),
    'scheduled'               => $this->request->getPost('scheduled'),
    'status'               => $this->request->getPost('status'),
    'po_items'               => $this->request->getPost('po_items'),
    'notes'               => $this->request->getPost('notes')

];
$result = $this->builder->update($data, ['po_no' => $data['po_no']]); 
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
    $po_no =  $this->request->getPost('po_no');
    $result = $this->builder->delete(['po_no' => $this->request->getPost($po_no)]);

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

public function process() {
    $po_no =  $this->request->getPost('po_no');
    $query = $this->db->table('nj_receiving_items')->select('*')->getWhere(['po_no'=> $po_no]);
    $results = $query->getResultArray();
    foreach ($results as $result) {
        $items[] = array(
            'po_line_no' => $result['po_line_no'],
            'sku' => $result['sku'],
            'status' => $result['status'],
            'description' => $result['description'],
            'po_no' => $result['po_no'],
            'supplier_code' => $result['supplier_code'],
            'length' => $result['length'],
            'uom' => $result['uom'],
            'total_pcs' => $result['total_pcs'],
            'total_qty' => $result['total_qty'],
            'unit_qty' => $result['unit_qty'],
            'total_units' => $result['total_units'],
            'total_received' => $result['total_received'],
            'total_damage' => $result['total_damage'],
            'notes' => $result['notes'],
            
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
        'message'   => showDangerMessage("No records found."),
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
            'po_line_no' => $item['po_line_no'],
            'status' => $item['status'],
            'unit_qty' => $item['unit_qty'],
            'total_units' => $item['total_units'],
        );
    }
    $this->db->table('nj_receiving_items')->updateBatch($data, 'po_line_no');
    if($data) {
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

public function startProcess() {
    $data = [
    'po_line_no'             => $this->request->getPost('po_line_no'),
     'unit_qty'             => $this->request->getPost('unit_qty'),
     'total_units'             => $this->request->getPost('total_units'),
     'status'             => $this->request->getPost('status'),
];
$result = $this->db->table('nj_receiving_items')->update($data, ['po_line_no' => $data['po_line_no']]); 
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

// Grab all the items from the process-item response and insert each item into the thornton_inventory table
public function receiveItem() {
    $items = $this->request->getPost('items');
    $data = [];
    foreach ($items as $item) {
        $data[] = array(
            'unit_id' => $item['unit_id'],
            'sku' => $item['sku'],
            'po_no' => $item['po_no'],
            'supplier_code' => $item['supplier_code'],
            'unit_qty' => $item['unit_qty'],
            'length' => $item['length'],
            'total' => $item['total_unit_qty'],
            'damage' => $item['damage'],
            'date_in' => $item['date_in'],
          
        );
    }
    // count the number of items in the array
    $count = count($data);
    // insert or update the inventory table
    $this->db->table('nj_inventory')->upsertBatch($data);
    // update the thornton_receiving_items table with the unit_qty and total_units where the total units is the count of the items just received
    $this->db->table('nj_receiving_items')->update(['unit_qty' => $data[0]['unit_qty'], 'total_units' => $count], ['po_no' => $data[0]['po_no']]);
    

    if($data) {
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