<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            if (!Schema::hasColumn('orders_products', 'tax_rate')) {
                $table->decimal('tax_rate', 5)->default(0.00)->after('price');
            }
            if (!Schema::hasColumn('orders_products', 'unit_tax')) {
                $table->decimal('unit_tax', 20, 3)->default(0.00)->after('tax_rate');
            }
        });

        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('total_tax', 20, 3)
                ->storedAs('unit_tax * quantity_ordered')
                ->comment('unit_tax * quantity_ordered')
                ->after('total_price');
        });
    }
};
