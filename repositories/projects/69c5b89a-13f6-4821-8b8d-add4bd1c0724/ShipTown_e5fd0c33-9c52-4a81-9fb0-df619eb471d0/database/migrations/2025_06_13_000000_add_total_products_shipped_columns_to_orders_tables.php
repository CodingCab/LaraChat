<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('total_products_shipped', 20, 3)
                ->storedAs('quantity_shipped * unit_sold_price')
                ->comment('quantity_shipped * unit_sold_price')
                ->after('total_discount');
        });

        Schema::table('orders_products_totals', function (Blueprint $table) {
            $table->decimal('total_products_shipped', 20, 3)
                ->default(0)
                ->after('total_price');
        });
    }
};
