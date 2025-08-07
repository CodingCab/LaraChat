<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\InventoryReservationReport;
use Illuminate\Http\Request;

class InventoryReservationsController extends Controller
{
    public function index(Request $request)
    {
        $report = new InventoryReservationReport;

        return $report->response($request);
    }
}
