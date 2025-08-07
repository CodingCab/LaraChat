<?php

use App\Models\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        try {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropForeign(['product_sku']);
            });
        } catch (\Exception $e) {
            //
        }

        Schema::table('inventory', function (Blueprint $table) {
            $table->string('product_sku', 50)->nullable(false)->change();

            $table->foreign('product_sku')
                ->references('sku')
                ->on('products')
                ->cascadeOnUpdate();
        });
    }
};
