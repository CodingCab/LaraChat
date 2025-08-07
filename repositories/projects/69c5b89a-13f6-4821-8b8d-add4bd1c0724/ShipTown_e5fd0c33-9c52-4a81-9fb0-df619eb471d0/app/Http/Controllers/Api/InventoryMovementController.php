<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementStoreRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\Inventory;
use App\Modules\Reports\src\Models\InventoryMovementsReport;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryMovementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $report = new InventoryMovementsReport;

        $resource = $report->queryBuilder()
            ->simplePaginate($request->get('per_page', 10));

        return InventoryMovementResource::collection($resource);
    }

    public function store(InventoryMovementStoreRequest $request): AnonymousResourceCollection
    {
        /** @var Inventory $inventory */
        $inventory = Inventory::query()
            ->where([
                'product_id' => $request->get('product_id'),
                'warehouse_id' => $request->get('warehouse_id'),
            ])
            ->first();

        $inventoryMovement = InventoryService::adjust(
            $inventory,
            data_get($request->validated(), 'quantity'),
            ['description' => data_get($request->validated(), 'description')],
        );

        return InventoryMovementResource::collection([$inventoryMovement]);
    }
}
