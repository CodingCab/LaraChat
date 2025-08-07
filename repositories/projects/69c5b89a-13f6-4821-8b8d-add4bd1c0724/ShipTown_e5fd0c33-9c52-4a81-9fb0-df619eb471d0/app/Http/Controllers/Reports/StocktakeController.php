<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Reports\src\Models\StocktakesReport;
use App\Traits\CsvFileResponse;
use Illuminate\Http\Request;

class StocktakeController extends Controller
{
    use CsvFileResponse;

    public function index(Request $request): mixed
    {
        $report = new StocktakesReport;

        return $report->response($request);
    }
}
