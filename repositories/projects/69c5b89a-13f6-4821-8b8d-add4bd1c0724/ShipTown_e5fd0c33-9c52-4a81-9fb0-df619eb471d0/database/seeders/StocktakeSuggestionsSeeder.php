<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\StocktakeSuggestion;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StocktakeSuggestionsSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::query()
            ->whereNotIn('code', ['999'])
            ->get()
            ->each(function (Warehouse $warehouse) {
                Inventory::query()
                    ->where(['warehouse_id' => $warehouse->getKey()])
                    ->inRandomOrder()
                    ->limit(10)
                    ->get()
                    ->each(function (Inventory $inventory) {
                        StocktakeSuggestion::query()->firstOrCreate([
                            'product_id' => $inventory->product_id,
                            'reason' => 'Manual stocktake request'
                        ], [
                            'inventory_id' => $inventory->getKey(),
                            'warehouse_id' => $inventory->warehouse_id,
                            'points' => 20,
                        ]);
                    });
            });
    }
}
