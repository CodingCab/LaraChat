<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\ActivityReport;
use Illuminate\Http\Request;

class ActivityLogController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new ActivityReport;

        return $report->response($request);
    }
}
