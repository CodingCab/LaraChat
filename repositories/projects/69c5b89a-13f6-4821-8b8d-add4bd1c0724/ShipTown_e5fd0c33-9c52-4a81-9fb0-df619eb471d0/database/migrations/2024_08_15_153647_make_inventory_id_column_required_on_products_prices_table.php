<?php

use App\Models\ProductPrice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        ProductPrice::query()
            ->whereNull('inventory_id')
            ->chunkById(5000, function ($records) {
                ProductPrice::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'inventory_id' => DB::raw('(SELECT id FROM inventory WHERE inventory.warehouse_id = products_prices.warehouse_id AND inventory.product_id = products_prices.product_id)'),
                    ]);

                usleep(10000); // 10ms
            });

        try {
            Schema::table('products_prices', function (Blueprint $table) {
                $table->dropForeign(['inventory_id']);
            });
        } catch (\Exception $e) {
            //
        }

        Schema::table('products_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable(false)->change();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();
        });
    }
};
