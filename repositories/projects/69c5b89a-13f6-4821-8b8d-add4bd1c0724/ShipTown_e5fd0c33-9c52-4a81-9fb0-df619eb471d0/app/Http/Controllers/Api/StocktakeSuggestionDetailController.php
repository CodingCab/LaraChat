<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\StocktakeSuggestions\src\Reports\StoctakeSuggestionsDetailedReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class StocktakeSuggestionDetailController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $report = new StoctakeSuggestionsDetailedReport;

        $query = $report->queryBuilder()
            ->simplePaginate(request()->get('per_page', 10));

        return JsonResource::collection($query);
    }
}
