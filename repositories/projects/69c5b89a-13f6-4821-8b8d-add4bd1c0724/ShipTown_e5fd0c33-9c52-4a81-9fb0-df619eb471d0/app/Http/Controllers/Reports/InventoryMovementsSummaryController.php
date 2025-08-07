<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\InventoryMovementsSummaryReport;
use Illuminate\Http\Request;

class InventoryMovementsSummaryController extends Controller
{
    public function index(Request $request)
    {
        $report = new InventoryMovementsSummaryReport;

        return $report->response($request);
    }
}
