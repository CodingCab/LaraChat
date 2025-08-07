<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->after('product_id', function ($table) {
                $table->boolean('product_exists')->nullable();
                $table->boolean('product_updated')->nullable();
                $table->boolean('inventory_updated')->nullable();
                $table->boolean('pricing_imported')->nullable();
                $table->boolean('aliases_imported')->nullable();
                $table->boolean('tags_added')->nullable();
                $table->boolean('tags_removed')->nullable();
            });
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->dropColumn([
                'product_exists',
                'product_updated',
                'inventory_updated',
                'pricing_imported',
                'aliases_imported',
                'tags_added',
                'tags_removed'
            ]);
        });
    }
};
