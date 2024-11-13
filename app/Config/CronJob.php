<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Daycry\CronJob\Scheduler;
use Daycry\CronJob\Loggers\Database as DatabaseLogger;
use Daycry\CronJob\Loggers\File as FileLogger;

class CronJob extends \Daycry\CronJob\Config\CronJob
{
    /**
     * Set true if you want save logs
     */
    public bool $logPerformance = true;

    /*
    |--------------------------------------------------------------------------
    | Log Saving Method
    |--------------------------------------------------------------------------
    |
    | Set to specify the REST API requires to be logged in
    |
    | 'file'   Save in files
    | 'database'  Save in database
    |
    */
    public string $logSavingMethod = 'database';

    public array $logSavingMethodClassMap = [
        'file' => FileLogger::class,
        'database' => DatabaseLogger::class
    ];

    /**
     * Directory
     */
    public string $filePath = WRITEPATH . 'cronJob/';

    /**
     * File Name in folder jobs structure
     */
    public string $fileName = 'jobs';

    /**
     * --------------------------------------------------------------------------
     * Maximum performance logs
     * --------------------------------------------------------------------------
     *
     * The maximum number of logs that should be saved per Job.
     * Lower numbers reduced the amount of database required to
     * store the logs.
     *
     * If you write 0 it is unlimited
     */
    public int $maxLogsPerJob = 3;

    /*
    |--------------------------------------------------------------------------
    | Database Group
    |--------------------------------------------------------------------------
    |
    | Connect to a database group for logging, etc.
    |
    */
    public ?string $databaseGroup = 'default';

    /*
    |--------------------------------------------------------------------------
    | Cronjob Table Name
    |--------------------------------------------------------------------------
    |
    | The table name in your database that stores cronjobs
    |
    */
    public string $tableName = 'cronjob';

    /*
    |--------------------------------------------------------------------------
    | Cronjob Notification
    |--------------------------------------------------------------------------
    |
    | Notification of each task
    |
    */
    public bool $notification = true;
    public string $from = 'cronjobs@gardenstatelumber.com';
    public string $fromName = 'Garden State Cronjobs';
    public string $to = 'sunny@gardenstatelumber.com';
    public string $toName = 'Sunita Joseph';

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Notification of each task
    |
    */
    public array $views = [
        'login'                       => '\Daycry\CronJob\Views\login',
        'dashboard'                   => '\Daycry\CronJob\Views\dashboard',
        'layout'                      => '\Daycry\CronJob\Views\layout',
        'logs'                        => '\Daycry\CronJob\Views\logs'
    ];

    /*
    |--------------------------------------------------------------------------
    | Dashboard login
    |--------------------------------------------------------------------------
    */
    public bool $enableDashboard = true;
    public string $username = 'cronjobs@gardenstatelumber.com';
    public string $password = 'Summertime2024!!';

    /*
    |--------------------------------------------------------------------------
    | Cronjobs
    |--------------------------------------------------------------------------
    |
    | Register any tasks within this method for the application.
    | Called by the TaskRunner.
    |
    | @param Scheduler $schedule
    */
    public function init(Scheduler $schedule)
    {
    
    $schedule->call(function() {
          \App\Controllers\CronJobs\Enable::transferFile("customers");
      })->named("Enable Customers FTP")->daily( '6:10 am' );
   
     $schedule->call(function() {
          \App\Controllers\CronJobs\Enable::transferFile("products");
      })->named("Enable Products FTP")->daily( '6:30 am' );
      
        $schedule->call(function() {
          \App\Controllers\CronJobs\Enable::transferFile("shipto");
      })->named("Enable Shipto FTP")->daily( '6:40 am' );
    
     $schedule->call(function() {
          \App\Controllers\CronJobs\Enable::transferFile("turnover");
      })->named("Enable Turnover FTP")->daily( '7:00 am' );
    
    // POs
    $schedule->call(function() {
          \App\Controllers\CronJobs\PoStock::store("po_stock");
      })->named("PO Stock Import")->daily( '4:30 pm' );
      
    $schedule->call(function() {
          \App\Controllers\CronJobs\PoStockItems::store("po_stock_items");
      })->named("PO Stock Items Import")->daily( '4:35 pm' );  
    /* 
    $schedule->call(function() {
          \App\Controllers\CronJobs\PoStock::store("po_stock_nj");
      })->named("PO  NJ Stock Import")->daily( '4:30 pm' );
      
    $schedule->call(function() {
          \App\Controllers\CronJobs\PoStockItems::store("po_stock_items_nj");
      })->named("PO NJ Stock Items Import")->daily( '4:35 pm' );  
    
          $schedule->call(function() {
          \App\Controllers\CronJobs\PoStock::store("po_stock_sc");
      })->named("PO SC Stock Import")->daily( '6:30 pm' );
      
    $schedule->call(function() {
          \App\Controllers\CronJobs\PoStockItems::store("po_stock_items_sc");
      })->named("PO SC Stock Items Import")->daily( '6:35 pm' );  
      */
      // DocuWare
      $schedule->call(function() {
          \App\Controllers\CronJobs\DocuWare::transferFile();
      })->named("DocuWare File Transfer")->daily( '6:40 pm' );
   
     //   $schedule->url(base_url('cron/manage-log'))->named("Agility File Transfer")->everyThirtyMinutes();
        // $schedule->command('foo:bar')->everyMinute();

        // $schedule->shell('cp foo bar')->daily( '11:00 pm' );

    }
}
