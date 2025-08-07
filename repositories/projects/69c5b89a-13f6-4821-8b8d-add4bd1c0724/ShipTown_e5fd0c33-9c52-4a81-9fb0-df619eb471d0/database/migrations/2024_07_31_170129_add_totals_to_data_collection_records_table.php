<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('unit_cost', 20, 3)->change();
            $table->decimal('unit_sold_price', 20, 3)->change();
            $table->decimal('unit_full_price', 20, 3)->change();
            $table->decimal('total_discount', 20)->change();

            $table->dropColumn('unit_discount');
        });

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('unit_discount', 20)
                ->nullable()
                ->storedAs('ROUND(unit_full_price - unit_sold_price, 3)')
                ->comment('ROUND(unit_full_price - unit_sold_price, 3)')
                ->after('unit_sold_price');
        });

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('total_cost', 20)
                ->nullable()
                ->storedAs('ROUND(quantity_scanned * unit_cost, 2)')
                ->comment('ROUND(quantity_scanned * unit_cost, 2)')
                ->after('price_source_id');

            $table->decimal('total_full_price', 20)
                ->nullable()
                ->storedAs('ROUND(quantity_scanned * unit_full_price, 2)')
                ->comment('ROUND(quantity_scanned * unit_full_price, 2)')
                ->after('total_cost');

            $table->decimal('total_sold_price', 20)
                ->nullable()
                ->storedAs('ROUND(quantity_scanned * unit_sold_price, 2)')
                ->comment('ROUND(quantity_scanned * unit_sold_price, 2)')
                ->after('unit_discount');

            $table->decimal('total_profit', 20)
                ->nullable()
                ->storedAs('ROUND((unit_sold_price * quantity_scanned) - (unit_cost * quantity_scanned), 2)')
                ->comment('ROUND((unit_sold_price * quantity_scanned) - (unit_cost * quantity_scanned), 2)')
                ->after('total_sold_price');
        });
    }
};
