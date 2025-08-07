<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collections', function (Blueprint $table) {
            $table->decimal('total_quantity_scanned', 20, 2)->nullable()->after('name');
            $table->decimal('total_cost', 20, 2)->nullable()->after('total_quantity_scanned');
            $table->decimal('total_full_price', 20, 2)->nullable()->after('total_cost');
            $table->decimal('total_discount', 20, 2)->nullable()->after('total_full_price');
            $table->decimal('total_sold_price', 20, 2)->nullable()->after('total_discount');
            $table->decimal('total_profit', 20, 2)->nullable()->after('total_sold_price');
        });
    }
};
