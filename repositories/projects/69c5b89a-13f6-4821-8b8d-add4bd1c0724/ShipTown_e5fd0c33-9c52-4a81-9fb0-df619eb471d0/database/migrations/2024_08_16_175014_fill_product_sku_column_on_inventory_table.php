<?php

use App\Models\Inventory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        do {
            $recordsUpdated = Inventory::query()
                ->leftJoin('products', 'products.id', '=', 'inventory.product_id')
                ->whereNull('inventory.product_sku')
                ->limit(5000)
                ->update(['inventory.product_sku' => DB::raw('products.sku')]);

            usleep(5000); // 5ms
        } while ($recordsUpdated > 0);
    }
};
