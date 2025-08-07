<?php

namespace App\Http\Controllers\Api\Modules\DpdIreland;

use App\Http\Controllers\Controller;
use App\Http\Resources\DpdIrelandConfigurationResource;
use App\Modules\DpdIreland\src\Client;
use App\Modules\DpdIreland\src\Http\Requests\DpdIrelandDestroyRequest;
use App\Modules\DpdIreland\src\Http\Requests\DpdIrelandIndexRequest;
use App\Modules\DpdIreland\src\Http\Requests\DpdIrelandStoreRequest;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Http\Resources\Json\JsonResource;

class DpdIrelandController extends Controller
{
    public function index(DpdIrelandIndexRequest $request): JsonResource
    {
        $connection = DpdIreland::firstOrFail();

        return DpdIrelandConfigurationResource::make($connection);
    }

    public function store(DpdIrelandStoreRequest $request): JsonResource
    {
        $connection = DpdIreland::updateOrCreate([], $request->validated());

        Client::clearCache();

        return DpdIrelandConfigurationResource::make($connection);
    }

    public function destroy(DpdIrelandDestroyRequest $request, int $connection_id): DpdIrelandConfigurationResource
    {
        $connection = DpdIreland::findOrFail($connection_id);

        $connection->delete();

        return DpdIrelandConfigurationResource::make($connection);
    }
}
