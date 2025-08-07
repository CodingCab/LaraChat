<?php

namespace App\Http\Controllers\Api\DataCollectorActions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DataCollectorActions\ImportAsSaleInventoryMovementRequest;
use App\Models\DataCollection;
use App\Modules\DataCollectorTransactions\src\Jobs\ImportAsSaleInventoryMovementJob;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportAsSaleInventoryMovementController extends Controller
{
    public function store(ImportAsSaleInventoryMovementRequest $request): JsonResource
    {
        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::findOrFail($request->validated('data_collection_id'));
        $dataCollection->update([
            'custom_uuid' => null,
            'currently_running_task' => ImportAsSaleInventoryMovementJob::class
        ]);
        $dataCollection->delete();

        ImportAsSaleInventoryMovementJob::dispatch($dataCollection->id);

        return JsonResource::make([
            'data_collection_id' => $dataCollection->id,
            'currently_running_task' => $dataCollection->currently_running_task,
        ]);
    }
}
