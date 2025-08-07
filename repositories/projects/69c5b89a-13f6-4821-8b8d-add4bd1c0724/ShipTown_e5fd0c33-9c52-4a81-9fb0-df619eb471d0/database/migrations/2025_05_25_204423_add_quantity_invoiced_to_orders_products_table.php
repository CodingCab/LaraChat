<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_products', function (Blueprint $table) {
            $table->decimal('quantity_invoiced', 20, 3)
                ->default(0)
                ->after('quantity_not_picked');
        });
    }
};
