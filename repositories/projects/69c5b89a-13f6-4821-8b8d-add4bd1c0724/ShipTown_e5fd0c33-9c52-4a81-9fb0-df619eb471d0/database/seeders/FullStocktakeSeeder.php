<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class FullStocktakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventory::query()->chunk(100, function ($inventories) {
            $inventories->each(function (Inventory $inventory) {
                InventoryService::stocktake($inventory, rand(1, 50));
            });
        });
    }
}
