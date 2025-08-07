<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('total_sold_price', 20, 3)
                ->storedAs('quantity_ordered * unit_sold_price')
                ->comment('quantity_ordered * unit_sold_price')
                ->after('unit_sold_price');

            $table->decimal('total_discount', 20, 3)
                ->storedAs('quantity_ordered * unit_discount')
                ->comment('quantity_ordered * unit_discount')
                ->after('total_sold_price');
        });
    }
};
