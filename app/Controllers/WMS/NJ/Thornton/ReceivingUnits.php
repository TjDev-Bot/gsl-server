<?php
namespace App\Controllers\WMS\NJ\Thornton;
use App\Controllers\BaseController;

class ReceivingUnits extends BaseController
{
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('receiving_units_thornton');
    }
    

  // Create units from the process-units ajax request
  public function createUnits() {
    $po_no = $this->request->getPost('po_no');
    $supplier_code = $this->request->getPost('supplier_code');
    $supplier_id = $this->request->getPost('supplier_id');
    $items = $this->request->getPost('items');

    $this->db->transStart();

    foreach ($items as $item) {
        $item['po_no'] = $po_no;
        $item['supplier_code'] = $supplier_code;
        $item['supplier_id'] = $supplier_id;
        $item['created_at'] = date('Y-m-d H:i:s');
        $item['updated_at'] = date('Y-m-d H:i:s');
        $this->builder->insert($item);
    }

    $this->db->transComplete();

    if ($this->db->transStatus() === FALSE) {
        return json_encode(['status' => 'error', 'message' => 'Failed to insert units.']);
    } else {
        return json_encode(['status' => 'success']);
    }
}
}
    

    
    