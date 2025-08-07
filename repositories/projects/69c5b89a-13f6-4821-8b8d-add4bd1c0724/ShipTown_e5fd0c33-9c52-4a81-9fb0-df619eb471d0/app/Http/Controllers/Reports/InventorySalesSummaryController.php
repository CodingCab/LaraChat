<?php

namespace App\Http\Controllers\Reports;

use App\Modules\Reports\src\Models\InventorySalesSummaryReport;

class InventorySalesSummaryController
{
    public function index()
    {
        $report = new InventorySalesSummaryReport;

        return $report->response();
    }
}
