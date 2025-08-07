<?php

namespace App\Http\Controllers\Api\Modules\StocktakeSuggestions;

use App\Http\Controllers\Controller;
use App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationController extends Controller
{
    public function index(): JsonResource
    {
        return JsonResource::make(StocktakeSuggestionsConfiguration::query()->first());
    }

    public function store(Request $request): JsonResource
    {
        $configuration = StocktakeSuggestionsConfiguration::query()->updateOrCreate(['id' => $request->input('id')], $request->all());

        return JsonResource::make($configuration);
    }
}
