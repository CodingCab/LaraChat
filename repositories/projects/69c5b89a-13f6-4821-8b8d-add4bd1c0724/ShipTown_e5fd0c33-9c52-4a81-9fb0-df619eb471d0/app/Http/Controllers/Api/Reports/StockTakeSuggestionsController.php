<?php

namespace App\Http\Controllers\Api\Reports;

use App\Modules\StocktakeSuggestions\src\Reports\StocktakeSuggestionReport;

class StockTakeSuggestionsController
{
    public function index()
    {
        return StocktakeSuggestionReport::json();
    }
}
