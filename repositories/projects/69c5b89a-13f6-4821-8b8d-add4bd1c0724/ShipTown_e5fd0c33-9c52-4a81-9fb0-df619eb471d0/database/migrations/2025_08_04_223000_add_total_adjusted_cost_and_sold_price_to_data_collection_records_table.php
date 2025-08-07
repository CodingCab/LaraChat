<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('total_adjusted_cost', 20)
                ->nullable()
                ->storedAs('ROUND(IFNULL(total_adjusted_quantity, 0) * IFNULL(unit_cost, 0), 2)')
                ->comment('ROUND(IFNULL(total_adjusted_quantity, 0) * IFNULL(unit_cost, 0), 2)')
                ->after('total_adjusted_quantity');

            $table->decimal('total_adjusted_sold_price', 20)
                ->nullable()
                ->storedAs('ROUND(IFNULL(total_adjusted_quantity, 0) * IFNULL(unit_sold_price, 0), 2)')
                ->comment('ROUND(IFNULL(total_adjusted_quantity, 0) * IFNULL(unit_sold_price, 0), 2)')
                ->after('total_adjusted_cost');
        });
    }

    public function down(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->dropColumn(['total_adjusted_cost', 'total_adjusted_sold_price']);
        });
    }
};