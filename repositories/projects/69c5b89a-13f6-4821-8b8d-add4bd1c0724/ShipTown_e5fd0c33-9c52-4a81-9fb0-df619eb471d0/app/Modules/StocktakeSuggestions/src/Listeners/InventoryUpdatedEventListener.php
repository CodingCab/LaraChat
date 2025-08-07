<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\StocktakeSuggestion;

class InventoryUpdatedEventListener
{
    public function handle(InventoryUpdatedEvent $event): void
    {
        if ($event->inventory->isAttributeChanged('last_counted_at')) {
            StocktakeSuggestion::query()->where(['inventory_id' => $event->inventory->id])->delete();
        }
    }
}
