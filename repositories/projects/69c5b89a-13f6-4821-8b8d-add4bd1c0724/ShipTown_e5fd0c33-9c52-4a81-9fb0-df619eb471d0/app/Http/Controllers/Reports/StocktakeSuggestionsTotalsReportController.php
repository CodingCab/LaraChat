<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\StocktakeSuggestions\src\Reports\StocktakeSuggestionsTotalsReport;
use Illuminate\Http\Request;

class StocktakeSuggestionsTotalsReportController extends Controller
{
    public function index(Request $request): mixed
    {
        $report = new StocktakeSuggestionsTotalsReport;

        return $report->response($request);
    }
}
