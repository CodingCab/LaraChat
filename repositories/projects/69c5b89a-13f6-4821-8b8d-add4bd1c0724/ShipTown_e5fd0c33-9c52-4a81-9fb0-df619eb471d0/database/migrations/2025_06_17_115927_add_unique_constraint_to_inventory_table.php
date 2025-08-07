<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $exists = DB::selectOne(
                "SHOW INDEX FROM inventory WHERE Key_name = 'inventory_product_id_warehouse_id_unique'"
            );

            if (! $exists) {
                $table->unique(['product_id', 'warehouse_id'], 'inventory_product_id_warehouse_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $exists = DB::selectOne(
                "SHOW INDEX FROM inventory WHERE Key_name = 'inventory_product_id_warehouse_id_unique'"
            );

            if ($exists) {
                $table->dropUnique('inventory_product_id_warehouse_id_unique');
            }
        });
    }
};
