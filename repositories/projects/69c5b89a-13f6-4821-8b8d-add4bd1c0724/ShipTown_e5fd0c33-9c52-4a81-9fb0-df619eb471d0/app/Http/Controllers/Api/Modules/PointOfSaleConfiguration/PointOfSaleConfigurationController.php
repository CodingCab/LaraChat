<?php

namespace App\Http\Controllers\Api\Modules\PointOfSaleConfiguration;

use App\Http\Controllers\Controller;
use App\Http\Requests\PointOfSaleConfiguration\UpdateRequest;
use App\Http\Resources\PointOfSaleConfigurationResource;
use App\Modules\PointOfSaleConfiguration\src\Models\PointOfSaleConfiguration;

class PointOfSaleConfigurationController extends Controller
{
    public function index(): PointOfSaleConfigurationResource
    {
        $configuration = PointOfSaleConfiguration::first();

        return new PointOfSaleConfigurationResource($configuration);
    }

    public function update(UpdateRequest $request, int $configurationId): PointOfSaleConfigurationResource
    {
        $config = PointOfSaleConfiguration::findOrFail($configurationId);

        $config->update($request->validated());

        return PointOfSaleConfigurationResource::make($config);
    }
}
