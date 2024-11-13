<?php
namespace App\Controllers\WMS\SC;
use App\Controllers\BaseController;

class Delivery extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('receiving_sc');
    }

// Main url for access this controller: https://gsl.jastech.co/wms/sc/delivery
    public function index() {
        // Write a function to query the database and get all the Mouldings in Codeigniter
        $data = [
            'title'   => 'SC Delivery Scheduler',
            'route'   => 'wms/sc/delivery',
    // https://gsl.jastech.co/wms/sc/delivery
        ];
        return view('wms/sc/delivery/index', $data);
       
    }
// https://gsl.jastech.co/wms/sc/delivery/read
    public function read() {
        // select * from receiving_sc order by delivery_date asc
        $query = $this->builder->orderBy('delivery_date', 'ASC')->get();
        $results = $query->getResultArray();
        echo json_encode($results);
    }

//https://gsl.jastech.co/wms/nj/delivery/delivery-dates
    public function deliveryDates() {
        $query = $this->builder->select('po_no, supplier_code, delivery_date, status, color, text_color')->get();
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

// https://gsl.jastech.co/wms/nj/delivery/get-po
    public function getPo() {
 
    // $po_no = $_POST['po_no'];
    // $po_no = filter_var($po_no, FILTER_SANITIZE_NUMBER_INT);
        $po_no =  $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);
    // SELECT * FROM po_open WHERE po_no = 7987
        $builder = $this->db->table('po_stock_sc')->getWhere(['po_no' => $po_no]);
        $po_header  = $builder->getRowArray();
        // SELECT sku FROM po_open_items WHERE po_no = 7987;
        $query = $this->db->table('po_stock_items_sc')->select('sku')->getWhere(['po_no'=> $po_no]);
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
                    'message'   => "No records found.",
                ];
            }
        echo json_encode($json);
    }
   // Capture form response from create form & sanitize
   // https://gsl.jastech.co/wms/sc/delivery/create
   /* "INSERT INTO nj_receivin g (po_no, verbal_po, branch_code, supplier_code...)
    VALUES ('7987', 'NULL, '10NJ...')";
*/
    public function create() {
        $data = [
            'po_no'             => $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT),
            'verbal_po'         => $this->request->getPost('verbal_po', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'branch_code'       => $this->request->getPost('branch_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'supplier_id'     => $this->request->getPost('supplier_id', FILTER_SANITIZE_NUMBER_INT),
            'supplier_code'     => $this->request->getPost('supplier_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'reference'         => $this->request->getPost('reference', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'description'       => $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'location'          => $this->request->getPost('location', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'booked_by'         => $this->request->getPost('user', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'delivery_date'     => $this->request->getPost('delivery_date'),
            'po_items'          => $this->request->getPost('po_items', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'notes'             => $this->request->getPost('notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        $result = $this->builder->insert($data);
        if($result) {
            $json = [
                'status'    => true,
                'message'   =>"New delivery successfully scheduled!"
            ];
        } else {
            $json = [
                'status'    => false,
                'message'   => "Something went wrong. Please try again!",
            ];
        }
        echo json_encode($json);
        }

// https://gsl.jastech.co/wms/nj/delivery/edit
// SELECT * from nj_receiving where po_no = 7987; 
public function edit() {
    // $po_no = $_POST['po_no'];
    // $po_no = filter_var($po_no, FILTER_SANITIZE_NUMBER_INT);
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
                'message'   => "Something went wrong. Please try again!",
    ];
    }
    echo json_encode($json);
}

// https://gsl.jastech.co/wms/nj/delivery/update
/*
UPDATE nj_receiving
SET location=value, delivery_date=value2,...
WHERE po_no=some_value AND id-some_value
*/
public function update() {

    $id    = $this->request->getPost('id', FILTER_SANITIZE_NUMBER_INT);
    $po_no = $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);
    
    $data = [
        'location'          => $this->request->getPost('location', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'delivery_date'     => $this->request->getPost('delivery_date'),
        'po_items'          => $this->request->getPost('po_items', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'notes'             => $this->request->getPost('notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
    ];
// update where the po_no and id are the same as the po_no in the request
$where = ['po_no' => $po_no, 'id' => $id];
$result = $this->builder->update($data, $where);
    if($result) {
        $json = [
        'status'    => true,
        'message'   => "Record was successfully updated!",
    ];
    } else {
        $json = [
        'status'    => false,
                'message'   => "Something went wrong. Please try again!",
    ];
    }
echo json_encode($json);
}

// https://gsl.jastech.co/wms/nj/delivery/delete
public function delete() {
    $data = [
    'po_no'             => $this->request->getPost('po_no'),
    'id'             => $this->request->getPost('id'),
];
$where = ['po_no' => $data['po_no'], 'id' => $data['id']];
$result = $this->builder->delete($where);

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


}