<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 20, 3)->nullable()->after('quantity_after');
            $table->decimal('unit_price', 20, 3)->nullable()->after('unit_cost');
        });
    }
};
