<?php

namespace App\Http\Controllers\Reports;

use App\Modules\StocktakeSuggestions\src\Reports\StocktakeSuggestionReport;
use Illuminate\Http\Request;

class StocktakeSuggestionsController
{
    public function index(Request $request): mixed
    {
        $report = new StocktakeSuggestionReport;

        return $report->response($request);
    }
}
