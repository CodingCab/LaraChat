<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\InventoryTotalsReport;
use Illuminate\Http\Request;

class InventoryTotalsController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new InventoryTotalsReport;

        return $report->response($request);
    }
}
