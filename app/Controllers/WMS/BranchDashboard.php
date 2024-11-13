<?php

namespace App\Controllers\WMS;

use App\Controllers\BaseController;

class BranchDashboard extends BaseController
{
    public function index($branch, $warehouse): string
    {
        // Convert the branch and warehouse values to uppercase
        $branchUpper = strtoupper($branch);
        $warehouseUpper = strtoupper($warehouse);

        // Prepare data for the view
        $data = [
            'title' => "{$branchUpper} - {$warehouseUpper} Dashboard",
        ];

        // Return the view with the data
        return view("wms/{$branch}/{$warehouse}/index", $data);
    }
}