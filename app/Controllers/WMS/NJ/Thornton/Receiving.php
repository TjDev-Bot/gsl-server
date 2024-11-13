<?php
namespace App\Controllers\WMS\NJ\Thornton;
use App\Controllers\BaseController;

class Receiving extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('receiving_nj');
    }

    public function index() {
        $data = [
            'title'   => '5 Thornton Receiving',
            'route'   => 'wms/nj/thornton/receiving',
        ];
        return view('wms/nj/thornton/receiving/index', $data);
    }
    public function read() {
        // select * from receiving_nj where location = '5 Thornton' and order by delivery_date asc
        $where = ['location' => '5 Thornton'];
        $query = $this->db->table('receiving_nj')->select('*')->getWhere($where);
        $results = $query->getResult();
        echo json_encode($results);
    }

    public function deliveryDates() {
        $where = ['location' => '5 Thornton'];
        $query = $this->db->table('receiving_nj')->select('po_no, supplier_code, delivery_date, status, color, text_color')->getWhere($where);
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
    public function items() {
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