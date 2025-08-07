<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products_prices', function (Blueprint $table) {
            $table->boolean('is_on_sale')->default(false)->after('price')
                ->generatedAs('sale_price_start_date IS NOT NULL & now() > sale_price_start_date');
        });
    }
};
