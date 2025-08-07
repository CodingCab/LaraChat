<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\PicksReport;
use App\Traits\CsvFileResponse;
use Illuminate\Http\Request;

class PickController extends Controller
{
    use CsvFileResponse;

    public function index(Request $request)
    {
        $report = new PicksReport;
        $report->setPerPage($request->get('per_page', 100));

        return $report->response($request);
    }
}
