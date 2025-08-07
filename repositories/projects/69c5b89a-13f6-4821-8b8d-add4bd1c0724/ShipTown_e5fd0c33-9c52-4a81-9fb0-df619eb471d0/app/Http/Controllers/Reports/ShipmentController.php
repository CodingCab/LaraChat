<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\OrderShipmentReport;
use Illuminate\Http\Request;

class ShipmentController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new OrderShipmentReport;

        return $report->response($request);
    }
}
