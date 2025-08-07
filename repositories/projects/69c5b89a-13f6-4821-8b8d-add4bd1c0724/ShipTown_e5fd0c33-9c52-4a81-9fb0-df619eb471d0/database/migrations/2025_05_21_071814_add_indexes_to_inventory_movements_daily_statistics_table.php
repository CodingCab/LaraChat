<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements_daily_statistics', function (Blueprint $table) {
            $table->index('inventory_id', 'imds_inventory_id_index');
            $table->index(['date', 'warehouse_code'], 'imds_date_warehouse_code_index');
        });
    }
};
