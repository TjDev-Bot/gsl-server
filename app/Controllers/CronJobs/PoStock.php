<?php

namespace App\Controllers\CronJobs;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class PoStock extends BaseController
{
    public static function store($file_name) {
        // Validate and sanitize file name
        $file_name = basename($file_name);
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $file_name)) {
            throw new \InvalidArgumentException('Invalid file name');
        }

        // Variables
        $inputFileType = 'Xlsx';
        $inputFileName = WRITEPATH . "uploads/CronJobs/POs/" . $file_name . ".xlsx";

        // Define a Read Filter function to read only specific columns
        $readFilter = function($column, $row, $worksheetName = '') {
            // Read columns A to G (1 to 7)
            return in_array($column, range('A', 'G'));
        };

        // Create a new Reader of the type defined in $inputFileType
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(TRUE);
        $reader->setReadFilter(new class($readFilter) implements IReadFilter {
            private $readFilter;
            public function __construct($readFilter) {
                $this->readFilter = $readFilter;
            }
            public function readCell($column, $row, $worksheetName = '') {
                return call_user_func($this->readFilter, $column, $row, $worksheetName);
            }
        });

        // Load the spreadsheet
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();

        $rowIterator = $worksheet->getRowIterator();
        $rows = [];
        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            $rows[] = $cells;
        }
        // Remove the first row (header)
        array_shift($rows);

        // Insert data to database to the po_no, verbal_po, branch_code, supplier_id, supplier_code, reference, description
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'po_no' => $row[0],
                'verbal_po' => $row[1],
                'branch_code' => $row[2],
                'supplier_id' => $row[3],
                'supplier_code' => $row[4],
                'reference' => $row[5],
                'description' => $row[6],
            ];
        }

        // Batch insert data to reduce the number of queries
        if (!empty($data)) {
            $db = \Config\Database::connect();
            $builder = $db->table($file_name);
            $builder->insertBatch($data);
        }

        // Free up memory
        unset($spreadsheet, $worksheet, $rows, $data);
    }
}