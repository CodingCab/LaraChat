<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_magento2api_products', function (Blueprint $table) {
            $table->index('connection_id');
            $table->index('base_price_sync_required');
            $table->index('special_price_sync_required');
            $table->index('sku');
            $table->index('exists_in_magento');
            $table->index('base_prices_fetched_at');
            $table->index('special_prices_fetched_at');
        });
    }
};
