<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners\OutdatedCounts\InventoryUpdatedEvent;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\StocktakeSuggestion;
use App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration;
use Illuminate\Support\Facades\Cache;

class AddOutdatedCountSuggestionListener
{
    public function handle(InventoryUpdatedEvent $event): void
    {
        $inventory = $event->inventory;

        if ($inventory->quantity === 0.00) {
            return;
        }

        $min_count_date = $this->getMinCountDate();

        if ($min_count_date === null) {
            return;
        }

        if ($inventory->in_stock_since !== null && $inventory->in_stock_since->isAfter($min_count_date)) {
            return;
        }

        if ($inventory->last_counted_at !== null && $inventory->last_counted_at->isAfter($min_count_date)) {
            return;
        }

        StocktakeSuggestion::query()->upsert([
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'points' => 1,
            'reason' => 'outdated count',
        ], ['inventory_id', 'reason']);
    }

    protected function getMinCountDate(): mixed
    {
        return Cache::get('stocktake_suggestions_min_count_date', function () {
            /** @var StocktakeSuggestionsConfiguration $configuration */
            $configuration = StocktakeSuggestionsConfiguration::query()->firstOrCreate();

            Cache::set('stocktake_suggestions_min_count_date', $configuration->min_count_date, 60);

            return $configuration->min_count_date;
        });
    }
}
