<?php

namespace App\Http\Controllers\Api\DataCollectorActions;

use App\Http\Requests\Api\DataCollectorActions\AddProductStoreRequest;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\ProductAlias;
use App\Modules\DataCollector\src\Jobs\DispatchDataCollectorRecalculateRequestJob;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class AddProductController
{
    public function store(AddProductStoreRequest $request): AnonymousResourceCollection
    {
        $fieldName = $request->has('quantity_scanned') ? 'quantity_scanned' : 'quantity_requested';

        $productId = ProductAlias::query()
            ->where(['alias' => $request->validated('sku_or_alias')])
            ->first('product_id')->product_id;

        $dataCollection = DataCollection::query()
            ->find($request->validated('data_collection_id'));

        $dataCollectionRecord = $dataCollection->records()
            ->where(['product_id' => $productId])
            ->first();

        if ($dataCollectionRecord) {
            $dataCollectionRecord->increment($fieldName, $request->validated($fieldName, 0));

            DispatchDataCollectorRecalculateRequestJob::dispatch($dataCollection);
            return JsonResource::collection(Arr::wrap($dataCollectionRecord));
        }

        $inventory = Inventory::query()
            ->with('prices')
            ->where(['product_id' => $productId, 'warehouse_id' => $dataCollection->warehouse_id])
            ->first();

        $params = [
            'data_collection_id' => $dataCollection->id,
            'unit_cost' => $inventory->prices->cost,
            'unit_full_price' => data_get($inventory, 'prices.price'),
            'unit_sold_price' => data_get($inventory, 'prices.current_price'),
            'price_source' => data_get($inventory, 'prices.price') > data_get($inventory, 'prices.current_price') ? 'SALE_PRICE' : null,
            'price_source_id' => null,
            'inventory_id' => $inventory->id,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
            'product_id' => $inventory->product_id,
            'sales_tax_code' => $inventory->prices->sales_tax_code,
            'quantity_requested' => 0,
        ];

        $params[$fieldName] = $request->validated($fieldName);

        DispatchDataCollectorRecalculateRequestJob::dispatch($dataCollection);

        return JsonResource::collection(Arr::wrap(DataCollectionRecord::query()->create($params)));
    }
}
