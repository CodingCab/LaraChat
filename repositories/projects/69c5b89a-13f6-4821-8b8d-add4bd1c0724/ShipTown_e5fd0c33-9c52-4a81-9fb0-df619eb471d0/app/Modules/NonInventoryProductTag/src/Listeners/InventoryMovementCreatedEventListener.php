<?php

namespace App\Modules\NonInventoryProductTag\src\Listeners;

use App\Events\InventoryMovement\InventoryMovementCreatedEvent;

class InventoryMovementCreatedEventListener
{
    public function handle(InventoryMovementCreatedEvent $event): void
    {
        $inventoryMovement = $event->inventoryMovement;

        if ($inventoryMovement->quantity_delta == 0) {
            return;
        }

        if ($inventoryMovement->product->hasTags(['non-inventory'])) {
            $inventoryMovement->quantity_before = 0;
            $inventoryMovement->quantity_after = 0;
            $inventoryMovement->save();
        }
    }
}
