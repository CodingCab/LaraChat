<?php

namespace App\Modules\InventoryMovementsStatistics\src\Listeners;

use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic;
use Illuminate\Support\Facades\DB;

class InventoryMovementCreatedEventListener
{
    public function handle(InventoryMovementCreatedEvent $event)
    {
        $this->extracted($event);
    }

    private function extracted(InventoryMovementCreatedEvent $event): void
    {
        $inventoryMovement = $event->inventoryMovement;

        DB::transaction(function () use ($inventoryMovement) {
            $recordsUpdated = InventoryMovementsStatistic::query()
                ->where('inventory_id', $inventoryMovement->inventory_id)
                ->where('type', $inventoryMovement->type)
                ->update([
                    'last7days_quantity_delta' => DB::raw('last7days_quantity_delta + '.$inventoryMovement->quantity_delta),
                    'last7days_min_movement_id' => DB::raw('IFNULL(last7days_min_movement_id, '.$inventoryMovement->id.')'),
                    'last7days_max_movement_id' => $inventoryMovement->id,
                    'last14days_quantity_delta' => DB::raw('last14days_quantity_delta + '.$inventoryMovement->quantity_delta),
                    'last14days_min_movement_id' => DB::raw('IFNULL(last7days_min_movement_id, '.$inventoryMovement->id.')'),
                    'last14days_max_movement_id' => $inventoryMovement->id,
                    'last28days_quantity_delta' => DB::raw('last28days_quantity_delta + '.$inventoryMovement->quantity_delta),
                    'last28days_min_movement_id' => DB::raw('IFNULL(last7days_min_movement_id, '.$inventoryMovement->id.')'),
                    'last28days_max_movement_id' => $inventoryMovement->id,
                ]);

            if ($recordsUpdated === 0) {
                InventoryMovementsStatistic::query()
                    ->create([
                        'type' => $inventoryMovement->type,
                        'inventory_id' => $inventoryMovement->inventory_id,
                        'product_id' => $inventoryMovement->product_id,
                        'warehouse_code' => $inventoryMovement->warehouse->code,
                        'last7days_quantity_delta' => $inventoryMovement->quantity_delta,
                        'last7days_min_movement_id' => $inventoryMovement->id,
                        'last7days_max_movement_id' => $inventoryMovement->id,
                        'last14days_quantity_delta' => $inventoryMovement->quantity_delta,
                        'last14days_min_movement_id' => $inventoryMovement->id,
                        'last14days_max_movement_id' => $inventoryMovement->id,
                        'last28days_quantity_delta' => $inventoryMovement->quantity_delta,
                        'last28days_min_movement_id' => $inventoryMovement->id,
                        'last28days_max_movement_id' => $inventoryMovement->id,
                    ]);
            }
        });
    }
}
