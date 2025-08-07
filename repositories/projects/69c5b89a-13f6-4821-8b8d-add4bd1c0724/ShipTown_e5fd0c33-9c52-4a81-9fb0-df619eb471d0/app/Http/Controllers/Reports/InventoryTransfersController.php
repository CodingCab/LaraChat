<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\InventoryTransferReport;
use Illuminate\Http\Request;

class InventoryTransfersController extends Controller
{
    public function index(Request $request)
    {
        $report = new InventoryTransferReport;

        return $report->response($request);
    }
}
