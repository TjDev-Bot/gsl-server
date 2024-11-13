<?php
namespace App\Controllers\WMS\NJ;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class Delivery extends BaseController
{
    protected $request;
    protected $response;
    protected $db;
    protected $builder;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->db = Database::connect();
        $this->builder = $this->db->table('receiving_stock');
    }

    // Route: GET /wms/{branch}/delivery
    public function index($branch)
    {
        // Convert the branch value to uppercase
        $branchUpper = strtoupper($branch);

        // Prepare data for the view
        $data = [
            'title' => "{$branchUpper} Delivery Scheduler",
            'route' => "wms/{$branch}/delivery",
        ];

        // Return the view with the data
        return view("wms/{$branch}/delivery/index", $data);
    }

    // Route: GET /wms/{branch}/delivery/read
    public function read($branch)
    {
        // Get the table builder for the specified branch
        $builder = $this->db->table('receiving_stock')->where('branch_code', $branch);
        // Query the table and order by delivery_date
        $query = $builder->orderBy('delivery_date', 'ASC')->get();
        // Get the results as an array
        $results = $query->getResultArray();
        // Return the results as a JSON response
        return $this->response->setJSON($results);
    }

    // Route: GET /wms/nj/delivery/delivery-dates
    public function deliveryDates()
    {
        // Get the table builder for the delivery_units_thornton table
        $builder = $this->db->table('delivery_units_thornton');
        // Select specific columns from the table
        $query = $builder->select('po_no, supplier_code, delivery_date, status, color, text_color')->get();
        // Get the results as an array
        $results = $query->getResultArray();
        // Prepare the data array
        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'po_no' => $result['po_no'],
                'supplier_code' => $result['supplier_code'],
                'delivery_date' => $result['delivery_date'],
                'status' => $result['status'],
                'color' => $result['color'],
                'text_color' => $result['text_color']
            ];
        }
        // Return the data as a JSON response
        return $this->response->setJSON($data);
    }

    // Route: POST /wms/nj/delivery/get-po
    public function getPo()
    {
        // Sanitize and validate input data
        $po_no = $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);

        // Query the po_stock_nj table for the given po_no
        $builder = $this->db->table('po_stock_nj');
        $po_header = $builder->getWhere(['po_no' => $po_no])->getRowArray();

        // Query the po_stock_items_nj table for the given po_no
        $query = $this->db->table('po_stock_items_nj')->select('sku')->getWhere(['po_no' => $po_no]);
        $results = $query->getResultArray();

        // Add the items to the po_header
        $po_header['items'] = $results;

        // Prepare the JSON response
        if ($po_header) {
            $json = [
                'status' => true,
                'data' => $po_header,
            ];
        } else {
            $json = [
                'status' => false,
                'message' => "No records found.",
            ];
        }

        // Return the JSON response
        return $this->response->setJSON($json);
    }

    // Route: POST /wms/nj/delivery/create
    public function create()
    {
        // Sanitize and validate input data
        $data = [
            'po_no'             => $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT),
            'verbal_po'         => $this->request->getPost('verbal_po', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 
            'branch_code'       => $this->request->getPost('branch_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'supplier_id'       => $this->request->getPost('supplier_id', FILTER_SANITIZE_NUMBER_INT),
            'supplier_code'     => $this->request->getPost('supplier_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'reference'         => $this->request->getPost('reference', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'description'       => $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'location'          => $this->request->getPost('location', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'booked_by'         => $this->request->getPost('user', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'delivery_date'     => $this->request->getPost('delivery_date'),
            'po_items'          => $this->request->getPost('po_items', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'notes'             => $this->request->getPost('notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        // Start transaction
        $this->db->transStart();

        // Insert data into the receiving_stock table
        $result = $this->builder->insert($data);

        // Complete the transaction
        $this->db->transComplete();

        // Prepare the JSON response
        if ($this->db->transStatus() === FALSE) {
            $json = [
                'status' => false,
                'message' => 'Transaction failed. Please try again!'
            ];
        } else {
            $json = [
                'status' => true,
                'message' => 'New delivery successfully scheduled!'
            ];
        }

        // Return the JSON response
        return $this->response->setJSON($json);
    }

    // Route: POST /wms/nj/delivery/edit
    public function edit()
    {
        // Sanitize and validate input data
        $po_no = $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);

        // Query the table for the given po_no
        $query = $this->builder->getWhere(['po_no' => $po_no]);
        $result = $query->getRow();

        // Prepare the JSON response
        if ($result) {
            $json = [
                'status' => true,
                'data' => $result,
            ];
        } else {
            $json = [
                'status' => false,
                'message' => "Something went wrong. Please try again!",
            ];
        }

        // Return the JSON response
        return $this->response->setJSON($json);
    }

    // Route: POST /wms/nj/delivery/update
    public function update()
    {
        // Sanitize and validate input data
        $id = $this->request->getPost('id', FILTER_SANITIZE_NUMBER_INT);
        $po_no = $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);

        $data = [
            'location' => $this->request->getPost('location', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'delivery_date' => $this->request->getPost('delivery_date'),
            'po_items' => $this->request->getPost('po_items', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'notes' => $this->request->getPost('notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        // Update where the po_no and id are the same as the po_no in the request
        $where = ['po_no' => $po_no, 'id' => $id];
        $result = $this->builder->update($data, $where);

        // Prepare the JSON response
        if ($result) {
            $json = [
                'status' => true,
                'message' => "Record was successfully updated!",
            ];
        } else {
            $json = [
                'status' => false,
                'message' => "Something went wrong. Please try again!",
            ];
        }

        // Return the JSON response
        return $this->response->setJSON($json);
    }

    // Route: POST /wms/nj/delivery/delete
    public function delete()
    {
        // Sanitize and validate input data
        $id = $this->request->getPost('id', FILTER_SANITIZE_NUMBER_INT);
        $po_no = $this->request->getPost('po_no', FILTER_SANITIZE_NUMBER_INT);

        // Delete where the po_no and id are the same as the po_no in the request
        $where = ['po_no' => $po_no, 'id' => $id];
        $result = $this->builder->delete($where);

        // Prepare the JSON response
        if ($result) {
            $json = [
                'status' => true,
                'message' => "Record was successfully deleted!",
            ];
        } else {
            $json = [
                'status' => false,
                'message' => "Something went wrong. Please try again!",
            ];
        }

        // Return the JSON response
        return $this->response->setJSON($json);
    }
}