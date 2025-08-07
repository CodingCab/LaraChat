<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\RestockingReport;
use App\Traits\CsvFileResponse;
use Illuminate\Http\Request;

class RestockingReportController extends Controller
{
    use CsvFileResponse;

    public function index(Request $request)
    {
        $report = new RestockingReport;

        return $report->response($request);
    }
}
