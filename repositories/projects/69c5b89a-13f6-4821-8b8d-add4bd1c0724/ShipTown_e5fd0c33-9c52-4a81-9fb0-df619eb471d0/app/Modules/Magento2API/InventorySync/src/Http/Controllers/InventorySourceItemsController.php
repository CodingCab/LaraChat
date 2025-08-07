<?php

namespace App\Modules\Magento2API\InventorySync\src\Http\Controllers;

use App\Abstracts\ReportController;
use App\Modules\Magento2API\InventorySync\src\Reports\InventorySourceItemsReport;
use Illuminate\Http\Request;

class InventorySourceItemsController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new InventorySourceItemsReport();

        return $report->response();
    }
}
