<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class RestockingProductsSeeder extends Seeder
{
    public function run(): void
    {
        $fulfilmentWarehouse = Warehouse::withAnyTagsOfAnyType('fulfilment')->first() ??
            Warehouse::factory()->create()->attachTag('fulfilment');

        $warehouse = Warehouse::where('code', 'DUB')->first() ??
            Warehouse::factory()->create(['code' => 'DUB']);

        $products = Product::factory()->count(10)->create();

        $products->each(function (Product $product) use ($fulfilmentWarehouse, $warehouse) {
            $sourceInventory = $product->inventory($fulfilmentWarehouse->code)->first();
            $destinationInventory = $product->inventory($warehouse->code)->first();

            InventoryService::adjust($sourceInventory, rand(20, 50), [
                'description' => 'stocktake for Restocking page sample',
            ]);

            InventoryService::adjust($destinationInventory, rand(1, 3), [
                'description' => 'stocktake for Restocking page sample',
            ]);

            $destinationInventory->refresh();
            InventoryService::sell($destinationInventory, -$destinationInventory->quantity, [
                'description' => 'sale for Restocking page sample',
            ]);
        });

        Inventory::query()
            ->where(['warehouse_code' => $warehouse->code])
            ->whereIn('product_id', $products->pluck('id'))
            ->eachById(function (Inventory $inventory) {
                $inventory->update([
                    'reorder_point' => 5,
                    'restock_level' => 10,
                ]);
            });
    }
}
