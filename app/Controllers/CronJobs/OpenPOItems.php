<?php

namespace App\Controllers\CronJobs;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class OpenPOItems extends BaseController
{
    protected $helpers = ['filesystem'];
    
    public function __construct() {
        $this->db = \Config\Database::connect('default');
        $this->builder = $this->db->table('po_open_items');
    }

    public function store() {
        // Variables
        $file_name = 'po_open_items_nj';
        $inputFileType = 'Xlsx';
        $inputFileName = WRITEPATH . "uploads/CronJobs/POs/" . $file_name . ".xlsx";
     
/*
 Create a new Reader of the type defined in $inputFileType */
 $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
 $reader->setReadDataOnly(FALSE);
 $reader->setReadEmptyCells(FALSE);
 $spreadsheet = $reader->load($inputFileName);
 $worksheet = $spreadsheet->getActiveSheet();

 
 $rowIterator = $worksheet->getRowIterator();
    $rows = [];
    foreach($rowIterator as $row){
        $cellIterator = $row->getCellIterator();
        $cells = [];
        foreach($cellIterator as $cell){
            $cells[] = $cell->getValue();
        }
        $rows[] = $cells;
    }
    // Remove the first row
    array_shift($rows);

    // Insert data to database to the po_no, verbal_po, branch_code, supplier_id, supplier_code, reference, description
    $data = [];
    foreach($rows as $row){
        $data[] = [
         'po_line_no' => $row[0],
         'stock_id' => $row[1],
         'sku' => $row[2],
         'po_no' => $row[3],
         'supplier_id' => $row[4],
         'supplier_code' => $row[5],
         'description' => $row[6],
         'length' => $row[7],
         'uom' => $row[8],
         'total_pcs' => $row[9],
         'total_qty' => $row[10],
        ];
    }
    // delete all the data in the database
    $this->builder->truncate();
    // insert the new data
    $this->builder->insertBatch($data);
    return $this->response->setJSON(['status' => 'success', 'message' => 'Data has been stored successfully']);
}


}
