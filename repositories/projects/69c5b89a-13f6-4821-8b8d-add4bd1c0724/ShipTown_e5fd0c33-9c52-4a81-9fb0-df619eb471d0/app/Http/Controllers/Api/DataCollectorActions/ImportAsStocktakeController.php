<?php

namespace App\Http\Controllers\Api\DataCollectorActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DataCollectorActions\ImportAsStocktakeStoreRequest;
use App\Models\DataCollection;
use App\Models\DataCollectionStocktake;
use App\Modules\DataCollector\src\Jobs\ImportAsStocktakeJob;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportAsStocktakeController extends Controller
{
    public function store(ImportAsStocktakeStoreRequest $request): JsonResource
    {
        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::findOrFail($request->validated('data_collection_id'));
        $dataCollection->update(['type' => DataCollectionStocktake::class, 'currently_running_task' => ImportAsStocktakeJob::class]);
        $dataCollection->delete();

        ImportAsStocktakeJob::dispatch($dataCollection->id);

        return JsonResource::make([
            'data_collection_id' => $dataCollection->id,
            'currently_running_task' => $dataCollection->currently_running_task,
        ]);
    }
}
