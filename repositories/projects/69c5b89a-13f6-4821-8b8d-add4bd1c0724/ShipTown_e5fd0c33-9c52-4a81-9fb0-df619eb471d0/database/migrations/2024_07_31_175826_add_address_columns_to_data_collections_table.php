<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collections', function (Blueprint $table) {
            $table->string('warehouse_code', 5)
                ->nullable()
                ->after('type')
                ->references('code')
                ->on('warehouses');

            $table->string('destination_warehouse_code', 5)
                ->nullable()
                ->after('warehouse_id')
                ->references('code')
                ->on('warehouses');

            $table->foreignId('shipping_address_id')
                ->nullable()
                ->after('total_sold_price')
                ->references('id')
                ->on('orders_addresses');

            $table->foreignId('billing_address_id')
                ->nullable()
                ->after('shipping_address_id')
                ->references('id')
                ->on('orders_addresses');
        });
    }
};
