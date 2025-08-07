<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            $table->string('product_sku')->nullable()->after('data_collection_id');

            $table->foreign('product_sku')
                ->references('sku')
                ->on('products')
                ->cascadeOnUpdate();
        });
    }
};
