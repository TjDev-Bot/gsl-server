<?php

namespace App\Controllers\CronJobs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use phpseclib3\Net\SFTP;


class Enable
{

    public static function transferFile($search) {
 
        $timestamp = time();
        if ($search == 'turnover') {
            // TURNOVER-20241015900-20241015900
            $file_name = strtoupper($search) . "-" . (date("Ymd",$timestamp)) . "-" . (date("YmdHis",$timestamp));
        } else {
            // CUSTOMERS-20241015900
            $file_name = strtoupper($search) . "-". (date("YmdHis",$timestamp));
        }
        // delete files in Transfer folder
        array_map('unlink', array_filter( 
            (array) array_merge(glob(WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/*")))); 
        // find file named "customers.xlsx"
        $raw_file = WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/" . $search . ".xlsx";
        
      // copy customers.xlsx in the Transfers folder

       copy($raw_file, WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".xlsx");

        $inputFileType = 'Xlsx';
        $inputFileName = WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".xlsx";
     
/*
 Create a new Reader of the type defined in $inputFileType */
 
 
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//  Load $inputFileName to a Spreadsheet Object 
$reader->setReadDataOnly(false);
$spreadsheet = $reader->load($inputFileName);
        
    // Export to CSV file.

$writer = IOFactory::createWriter($spreadsheet, "Csv");
$writer->setSheetIndex(0);   // Select which sheet to export.
$writer->setDelimiter(',');  // Set delimiter.
 
$writer->save(WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".csv");


// Important: You should have read and write permissions to read
// the folder and write the zip file

$csvFile = WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".csv";
$zipFile = WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".zip";
$okFile = WRITEPATH . "uploads/CronJobs/ThirdParty/Enable/Transfer/" . $file_name . ".zip.ok";

$zip = new \ZipArchive();
$zip->open($zipFile, ZipArchive::OVERWRITE|ZipArchive::CREATE);
$zip->setPassword('dQH8W=NS-u4f8:DhldDSgd;dLYKrN.TZ');
$zip->addFile($csvFile, $file_name . ".csv");
$zip->setCompressionIndex(0, ZipArchive::CM_STORE);
$zip->setEncryptionName($file_name . ".csv", ZipArchive::EM_AES_256);
$zip->close();
echo 'Zip file created.';
fopen($okFile, "w");
        
    $host = 'sftp001.us.trading-programs.enable.com';
    $user = 'sftpus001.gsl';
    $password = '6Z3rd0/UZEtcopvGFY9+it/RLC0QRJXy';

    $sftp = new SFTP($host);

    if (!$sftp->login($user, $password)) {
        throw new \Exception('Could not initialize SFTP subsystem.');
    }

    echo 'Login successful';
    
    $zip_upload = $zipFile;
    $remote_zip_path = '/Imports/'. $file_name . ".zip";
    $remote_ok_path = '/Imports/'. $file_name . ".zip.ok";

$sftp->put($remote_zip_path, $zipFile, SFTP::SOURCE_LOCAL_FILE);
$sftp->put($remote_ok_path, $okFile, SFTP::SOURCE_LOCAL_FILE);


    }
 
}


