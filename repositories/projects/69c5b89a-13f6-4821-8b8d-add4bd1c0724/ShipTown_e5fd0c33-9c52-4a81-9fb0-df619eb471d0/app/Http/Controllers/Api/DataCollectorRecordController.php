<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataCollectionRecordUpdateRequest;
use App\Http\Requests\DataCollectorStoreRequest;
use App\Http\Resources\DataCollectionRecordResource;
use App\Models\DataCollectionRecord;
use App\Modules\DataCollector\src\Jobs\DispatchDataCollectorRecalculateRequestJob;
use App\Modules\Reports\src\Models\DataCollectionReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DataCollectorRecordController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $report = new DataCollectionReport;

        $resource = $report->queryBuilder()
            ->orderByRaw('
                data_collection_records.is_fully_scanned ASC,
                data_collection_records.is_requested ASC,
                (data_collection_records.quantity_scanned > 0) ASC,
                data_collection_records.is_over_scanned ASC,
                shelf_location ASC,
                data_collection_records.quantity_to_scan DESC,
                data_collection_records.id DESC
            ')
            ->simplePaginate(request()->get('per_page', 10))
            ->appends(request()->query());

        return JsonResource::collection($resource);
    }

    public function store(DataCollectorStoreRequest $request): DataCollectionRecordResource
    {
        /** @var DataCollectionRecord $collectionRecord */
        $collectionRecord = DataCollectionRecord::query()->firstOrCreate([
            'data_collection_id' => $request->validated('data_collection_id'),
            'inventory_id' => $request->validated('inventory_id'),
            'price_source' => null,
        ], [
            'product_id' => $request->validated('product_id'),
            'warehouse_code' => $request->validated('warehouse_code'),
            'warehouse_id' => $request->validated('warehouse_id'),
            'unit_cost' => 0,
            'unit_full_price' => 0,
            'unit_sold_price' => 0,
        ]);

        $collectionRecord->update([
            'unit_cost' => $collectionRecord->prices->cost,
            'unit_full_price' => $collectionRecord->prices->price,
            'unit_sold_price' => $collectionRecord->prices->price,
            'sales_tax_code' => $collectionRecord->product->default_tax_code,
            'quantity_scanned' => $collectionRecord->quantity_scanned + $request->validated('quantity_scanned', 0),
            'quantity_requested' => $collectionRecord->quantity_requested + $request->validated('quantity_requested', 0),
        ]);

        return DataCollectionRecordResource::make($collectionRecord);
    }

    public function update(DataCollectionRecordUpdateRequest $request, int $id): DataCollectionRecordResource
    {
        $attributes = $request->validated();

        /** @var DataCollectionRecord $collectionRecord */
        $collectionRecord = DataCollectionRecord::query()->findOrFail($id);

        $collectionRecord->update($attributes);

        DispatchDataCollectorRecalculateRequestJob::dispatchAfterResponse($collectionRecord->dataCollection);

        return DataCollectionRecordResource::make($collectionRecord);
    }
}
