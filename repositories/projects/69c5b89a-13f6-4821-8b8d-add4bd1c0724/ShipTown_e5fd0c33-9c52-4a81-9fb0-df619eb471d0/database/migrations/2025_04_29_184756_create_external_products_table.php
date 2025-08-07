<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->nullable();
            $table->string('external_id')->nullable();
            $table->string('external_sku')->nullable();
            $table->string('external_name')->nullable();
            $table->float('external_quantity')->nullable();
            $table->float('external_price', 3)->nullable();
            $table->float('external_sale_price', 3)->nullable();
            $table->dateTime('external_sale_price_start_datetime')->nullable();
            $table->dateTime('external_sale_price_end_datetime')->nullable();
            $table->float('external_weight', 3)->nullable();
            $table->string('external_weight_unit')->nullable();
            $table->float('external_length')->nullable();
            $table->float('external_width')->nullable();
            $table->float('external_height')->nullable();
            $table->json('raw_data');
            $table->timestamps();
        });
    }
};
