<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add billing_address_id column to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('billing_address_id')->nullable()->after('shipping_address_id');
            $table->foreign('billing_address_id')
                ->on('orders_addresses')
                ->references('id')
                ->onDelete('set null');
        });
    }
};
