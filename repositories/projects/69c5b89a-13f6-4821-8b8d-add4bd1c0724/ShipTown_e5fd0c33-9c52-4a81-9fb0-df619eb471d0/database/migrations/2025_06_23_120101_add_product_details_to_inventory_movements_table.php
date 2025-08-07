<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->after('occurred_at');
            $table->string('name', 100)->nullable()->after('sku');
            $table->string('department')->nullable()->after('name');
            $table->string('category')->nullable()->after('department');
        });
    }
};
