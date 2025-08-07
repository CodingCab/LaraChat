<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // do not merge - to be deleted
        Schema::create('inventory_movements_temp', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_first_movement')->nullable();
            $table->foreignId('inventory_id');
            $table->foreignId('product_id');
            $table->string('warehouse_code', 5)->nullable();
            $table->foreignId('warehouse_id');
            $table->foreignId('user_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('quantity_delta', 20);
            $table->decimal('quantity_before', 20);
            $table->decimal('quantity_after', 20);
            $table->string('description', 50);
            $table->string('custom_unique_reference_id')->nullable()->unique();
            $table->unsignedBigInteger('previous_movement_id')->nullable()->unique();
            $table->timestamps();

            $table->index('type');
            $table->index('is_first_movement');

            $table->foreign('previous_movement_id')
                ->references('id')
                ->on('inventory_movements')
                ->restrictOnDelete();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->restrictOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->restrictOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->restrictOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
