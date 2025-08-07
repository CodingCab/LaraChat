<?php

namespace App\Modules\InventoryMovements\src\Listeners;

use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use Carbon\Carbon;

class InventoryMovementCreatedEventListener
{
    public function handle(InventoryMovementCreatedEvent $event): void
    {
        $movement = $event->inventoryMovement;

        if ($movement->inventory->last_movement_at > $movement->occurred_at) {
            return;
        }

        $attributes = [
            'recount_required' => true,
            'quantity' => $movement->quantity_after,
            'last_movement_id' => $movement->id,
            'first_movement_at' => Carbon::parse($movement->occurred_at)->min($movement->inventory->first_movement_at)->toDateTimeString(),
            'last_movement_at' => Carbon::parse($movement->occurred_at)->max($movement->inventory->last_movement_at)->toDateTimeString(),
        ];

        if ($movement->type = $movement::TYPE_SALE) {
            $attributes['first_sold_at'] = Carbon::parse($movement->occurred_at)->min($movement->inventory->first_sold_at)->toDateTimeString();
            $attributes['last_sold_at'] = Carbon::parse($movement->occurred_at)->max($movement->inventory->last_sold_at)->toDateTimeString();
        }

        if ($movement->type = $movement::TYPE_STOCKTAKE) {
            $attributes['first_counted_at'] = Carbon::parse($movement->occurred_at)->min($movement->inventory->first_counted_at)->toDateTimeString();
            $attributes['last_counted_at'] = Carbon::parse($movement->occurred_at)->max($movement->inventory->last_counted_at)->toDateTimeString();
        }

        if ($movement->quantity_delta > 0) {
            $attributes['first_received_at'] = Carbon::parse($movement->occurred_at)->min($movement->inventory->first_received_at)->toDateTimeString();
            $attributes['last_received_at'] = Carbon::parse($movement->occurred_at)->max($movement->inventory->last_received_at)->toDateTimeString();
        }

        if ($movement->quantity_before = 0) {
            $attributes['in_stock_since'] = Carbon::parse($movement->occurred_at)->max($movement->inventory->in_stock_since)->toDateTimeString();
        }

        $movement->inventory->update($attributes);
    }
}
