<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\PurchaseOrderReport;
use Illuminate\Http\Request;

class PurchaseOrdersController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new PurchaseOrderReport;

        return $report->response($request);
    }
}
