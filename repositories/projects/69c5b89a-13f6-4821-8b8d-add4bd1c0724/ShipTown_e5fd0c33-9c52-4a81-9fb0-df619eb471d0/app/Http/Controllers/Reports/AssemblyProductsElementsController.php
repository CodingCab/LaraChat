<?php

namespace App\Http\Controllers\Reports;

use App\Abstracts\ReportController;
use App\Modules\AssemblyProducts\src\Reports\AssemblyProductsElementsReport;
use Illuminate\Http\Request;

class AssemblyProductsElementsController extends ReportController
{
    public function index(Request $request): mixed
    {
        $report = new AssemblyProductsElementsReport;

        return $report->response($request);
    }
}
