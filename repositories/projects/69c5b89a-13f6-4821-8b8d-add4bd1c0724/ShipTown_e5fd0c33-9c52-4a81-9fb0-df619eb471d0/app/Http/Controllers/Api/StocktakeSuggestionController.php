<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\StocktakeSuggestions\src\Reports\StocktakeSuggestionReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class StocktakeSuggestionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $report = new StocktakeSuggestionReport;

        return JsonResource::collection($report->simplePaginatedCollection());
    }
}
