<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->dropColumn('stock_items_fetched_at');
            $table->dropColumn('stock_items_raw_import');
            $table->dropColumn('is_in_stock');
            $table->dropColumn('quantity');

            $table->unsignedBigInteger('product_price_id')->nullable()
                ->after('connection_id');

            $table->boolean('base_price_sync_required')->nullable()->after('product_price_id');
            $table->boolean('special_price_sync_required')->nullable()->after('base_price_sync_required');

            $table->foreign('product_price_id')
                ->references('id')
                ->on('products_prices');
        });
    }
};
