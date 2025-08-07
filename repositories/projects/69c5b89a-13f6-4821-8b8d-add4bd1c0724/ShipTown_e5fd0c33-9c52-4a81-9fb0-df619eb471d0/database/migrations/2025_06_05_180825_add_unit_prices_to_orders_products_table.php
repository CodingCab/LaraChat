<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('unit_full_price', 20, 3)->default(0.00)->after('price');
            $table->decimal('unit_discount', 20, 3)->default(0.00)->after('unit_full_price');
            $table->decimal('unit_sold_price', 20, 3)->default(0.00)->after('unit_discount');
        });
    }
};
