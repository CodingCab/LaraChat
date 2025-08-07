<?php

namespace App\Modules\Fakturowo\src\Http\Controllers\Api\Modules;

use App\Http\Controllers\Controller;
use App\Modules\Fakturowo\src\Http\Requests\FakturowoConfiguration\StoreRequest;
use App\Modules\Fakturowo\src\Http\Resources\FakturowoConfigurationResource;
use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FakturowoConfigurationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = FakturowoConfiguration::getSpatieQueryBuilder()->defaultSort('id');

        return FakturowoConfigurationResource::collection($this->getPaginatedResult($query, 999));
    }

    public function store(StoreRequest $request): FakturowoConfigurationResource
    {
        $config = FakturowoConfiguration::create($request->validated());

        return FakturowoConfigurationResource::make($config);
    }

    public function destroy(int $configuration_id): FakturowoConfigurationResource
    {
        $config = FakturowoConfiguration::findOrFail($configuration_id);
        $config->delete();

        return FakturowoConfigurationResource::make($config);
    }
}
