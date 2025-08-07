<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('modules_magento2msi_products');
        Schema::dropIfExists('modules_magento2msi_requests');
        Schema::dropIfExists('modules_magento2msi_connections');

        Schema::create('modules_magento2msi_connections', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('base_url');
            $table->string('magento_source_code')->nullable();
            $table->unsignedBigInteger('inventory_source_warehouse_tag_id')->nullable();
            $table->longText('api_access_token');
            $table->timestamps();
        });

        Schema::create('modules_magento2msi_inventory_source_items', function (Blueprint $table) {
            $table->id();
            $table->string('custom_uuid')->nullable()->unique();
            $table->string('sku', 255);
            $table->string('source_code')->nullable();
            $table->boolean('exists_in_magento')->nullable();
            $table->boolean('source_assigned')->nullable();
            $table->boolean('sync_required')->nullable();
            $table->timestamp('inventory_source_items_fetched_at')->nullable();
            $table->decimal('quantity', 20)->nullable();
            $table->boolean('status')->nullable();
            $table->unsignedBigInteger('magento_product_id')->nullable();
            $table->string('magento_product_type')->nullable();
            $table->unsignedBigInteger('connection_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_totals_by_warehouse_tag_id');
            $table->json('inventory_source_items')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'sku'], 'unique_connection_id_sku');
            $table->index('connection_id');
            $table->index('product_id');
            $table->index('inventory_totals_by_warehouse_tag_id', 'inventory_totals_by_warehouse_tag_id_index');
            $table->index('exists_in_magento', 'exists_in_magento_index');
            $table->index('source_assigned', 'source_assigned_index');
            $table->index('sync_required', 'sync_required_index');
            $table->index('magento_product_id', 'magento_product_id_index');
            $table->index('magento_product_type', 'magento_product_type_index');
            $table->index('custom_uuid', 'custom_uuid_index');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_magento2msi_connections')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('inventory_totals_by_warehouse_tag_id', 'inventory_totals_by_warehouse_tag_id')
                ->references('id')
                ->on('inventory_totals_by_warehouse_tag')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('sku')
                ->references('sku')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
