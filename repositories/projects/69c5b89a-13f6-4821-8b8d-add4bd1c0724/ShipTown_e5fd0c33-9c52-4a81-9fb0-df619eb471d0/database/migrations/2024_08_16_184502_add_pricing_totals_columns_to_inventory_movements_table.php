<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('total_cost', 20, 3)
                ->storedAs('ROUND(quantity_delta * unit_cost, 3)')
                ->comment('ROUND(quantity_delta * unit_cost, 3)')
                ->after('unit_price');

            $table->decimal('total_price', 20, 3)
                ->storedAs('ROUND(quantity_delta * unit_price, 3)')
                ->comment('ROUND(quantity_delta * unit_price, 3)')
                ->after('total_cost');
        });
    }
};
