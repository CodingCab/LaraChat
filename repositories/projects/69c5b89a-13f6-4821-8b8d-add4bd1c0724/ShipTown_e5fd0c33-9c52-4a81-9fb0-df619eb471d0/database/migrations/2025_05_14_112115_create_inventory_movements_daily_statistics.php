<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('inventory_movements_daily_statistics');

        Schema::create('inventory_movements_daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('warehouse_code', 5);
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('last_inventory_movement_sequence_number');
            $table->timestamps();
        });

        Schema::table('inventory_movements_daily_statistics', function (Blueprint $table) {
            $table->foreign('last_inventory_movement_sequence_number', 'lm_sequence_number_fk')
                ->references('id')
                ->on('inventory_movements')
                ->onDelete('cascade');

            $table->foreign('inventory_id', 'inventory_id_fk')
                ->references('id')
                ->on('inventory')
                ->onDelete('cascade');

            $table->foreign('warehouse_code', 'warehouse_code_fk')
                ->references('code')
                ->on('warehouses')
                ->onDelete('cascade');

            $table->index(['date', 'inventory_id'], 'idx_date_inventory');
            $table->index(['inventory_id', 'date'], 'inx_inventory_date');

            $table->unique(['date', 'inventory_id'], 'unique_date_warehouse_inventory');
        });
    }
};
