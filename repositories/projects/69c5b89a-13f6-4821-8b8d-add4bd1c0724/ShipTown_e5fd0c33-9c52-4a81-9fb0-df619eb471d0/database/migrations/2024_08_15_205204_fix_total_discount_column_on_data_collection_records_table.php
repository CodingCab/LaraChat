<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->dropColumn('total_discount');
        });

        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->decimal('total_discount', 20, 3)
                ->storedAs('ROUND(quantity_scanned * (unit_full_price - unit_sold_price), 3)')
                ->comment('ROUND(quantity_scanned * (unit_full_price - unit_sold_price), 3)')
                ->after('total_full_price');
        });
    }
};
