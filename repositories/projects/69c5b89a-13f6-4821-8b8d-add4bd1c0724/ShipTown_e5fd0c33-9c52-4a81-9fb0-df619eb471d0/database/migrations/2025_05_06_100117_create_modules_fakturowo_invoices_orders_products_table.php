<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_fakturowo_invoices_orders_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable();
            $table->foreignId('order_id');
            $table->foreignId('orders_products_id');
            $table->decimal('quantity_invoiced', 20, 3);
            $table->timestamps();

            $table->foreign('invoice_id', 'fk_invoice_id')
                ->references('id')
                ->on('modules_fakturowo_invoices')
                ->onDelete('cascade');

            $table->foreign('order_id', 'fk_order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->foreign('orders_products_id', 'fk_ops_id')
                ->references('id')
                ->on('orders_products')
                ->onDelete('cascade');
        });
    }
};
