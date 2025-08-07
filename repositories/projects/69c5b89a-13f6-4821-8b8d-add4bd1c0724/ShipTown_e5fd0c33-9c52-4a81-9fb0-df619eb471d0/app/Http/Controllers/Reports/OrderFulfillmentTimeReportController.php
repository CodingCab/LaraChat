<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\OrderFulfillmentTimeReport;
use Illuminate\Http\Request;

class OrderFulfillmentTimeReportController extends Controller
{
    public function index(Request $request)
    {
        $report = new OrderFulfillmentTimeReport();

        return $report->response($request);
    }
}
