<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\Reports\src\Models\ProductsRequiredReport;
use Illuminate\Http\Request;

class ProductsRequiredReportController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new ProductsRequiredReport();

        return $report->response($request);
    }
}
