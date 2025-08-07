<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\InventoryReport;
use Illuminate\Http\Request;

class InventoryController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new InventoryReport;

        return $report->response($request);
    }
}
