<?php

namespace App\Controllers\CronJobs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class DocuWare 
{

    public static function transferFile() {

        $file_name = 'po_open_docuware_latest';
        array_map('unlink', array_filter( 
            (array) array_merge(glob(WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/*")))); 
        
      //  delete_files(WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/");
        $raw_file = WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/" . $file_name . ".xlsx";
        
        // Create file
        // copy file to transfer folder and rename to $file_name from $file_name above
        // $file_name = $file_name . ".xlsx";

       copy($raw_file, WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/" . $file_name . ".xlsx");

        $inputFileType = 'Xlsx';
        $inputFileName = WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/" . $file_name . ".xlsx";
     
/*
 Create a new Reader of the type defined in $inputFileType */
 
 
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//  Load $inputFileName to a Spreadsheet Object 
$reader->setReadDataOnly(false);
$spreadsheet = $reader->load($inputFileName);
        
    // Export to CSV file.

$writer = IOFactory::createWriter($spreadsheet, "Csv");
$writer->setSheetIndex(0);   // Select which sheet to export.
$writer->setDelimiter(';');  // Set delimiter.
 
$writer->save(WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/" . $file_name . ".csv");

        $ftp_server = 'ftp.docuware-online.com';
        $ftp_user_name = 'anonymous';
        $ftp_user_pass = '';
        $file = WRITEPATH . "uploads/CronJobs/ThirdParty/DocuWare/Transfer/po_open_docuware_latest.csv";
        $ftp = ftp_connect($ftp_server); 
        $login_result = ftp_login($ftp, $ftp_user_name, $ftp_user_pass);
        ftp_pasv($ftp, true);
        
        if (ftp_alloc($ftp, filesize($file), $result)) {
  echo "Space successfully allocated on server.  Sending $file.\n";
  ftp_put($ftp, '/YzkJJKdFfcXE0yZbtRCInJ2WlQyVVxlsGXYpL4CS/data/po_open_docuware_latest.csv', $file, FTP_BINARY);
} else {
  echo "Unable to allocate space on server.  Server said: $result\n";
}

ftp_close($ftp);

}
 
}