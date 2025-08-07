<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_fakturowo_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('fakturowo_invoice_id')->nullable();
            $table->string('filename')->nullable();
            $table->string('fakturowo_invoice_url')->nullable();
            $table->timestamps();

            $table->fullText(['fakturowo_invoice_id']);

            $table->foreign('order_id', 'fi_order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }
};
