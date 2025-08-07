<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\HeartbeatsReport;
use Illuminate\Http\Request;

class HeartbeatsReportController extends Controller
{
    public function index(Request $request): mixed
    {
        $report = new HeartbeatsReport();

        return $report->response($request);
    }
}
