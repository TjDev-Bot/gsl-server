<?php

namespace App\Controllers\CronJobs;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class PoStockItems extends BaseController
{
    public static function store($file_name) {
        // Variables
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
         'po_line_no'   => $row[0],
         'po_no'        => $row[1],
         'stock_id'     => $row[2],
         'sku'          => $row[3],
         'supplier_id'  => $row[4],
         'description'  => $row[5],
         'length'       => $row[6],
         'status'       => $row[7],
         'order_uom'    => $row[8],
         'stock_uom'    => $row[9],
         'unit_uom'     => $row[10],
         'order_qty'    => $row[11],
         'stock_qty'    => $row[12],
         'unit_qty'     => $row[13],
         'total_units'  => $row[14],
        ];
    }
    $db = \Config\Database::connect('default');
    $builder = $db->table($file_name);
    // delete all the data in the database
    $builder->truncate();
    // insert each row as a transaction
    $builder->insertBatch($data);
    return 'Data has been stored successfully';
    
}
}