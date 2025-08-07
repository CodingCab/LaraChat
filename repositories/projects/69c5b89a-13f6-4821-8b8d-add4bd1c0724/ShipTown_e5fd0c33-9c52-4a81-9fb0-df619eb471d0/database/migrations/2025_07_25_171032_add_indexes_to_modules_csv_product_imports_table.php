<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            // Primary filtering index for processing records by file
            $table->index(['file_id', 'processed_at'], 'idx_file_processed');
            
            // Index for SKU lookups when joining with products_aliases
            $table->index('sku', 'idx_sku');
            
            // Index for product-based queries
            $table->index(['product_id', 'processed_at'], 'idx_product_processed');
            
            // Composite index for product existence checks
            $table->index(['file_id', 'product_exists', 'processed_at'], 'idx_file_exists_processed');
            
            // Index for tags processing
            $table->index(['file_id', 'product_id', 'tags_add', 'tags_added', 'processed_at'], 'idx_tags_add_processing');
            $table->index(['file_id', 'product_id', 'tags_remove', 'tags_removed', 'processed_at'], 'idx_tags_remove_processing');
            
            // Index for alias imports
            $table->index(['file_id', 'product_id', 'alias', 'aliases_imported', 'processed_at'], 'idx_alias_processing');
            
            // Index for inventory updates
            $table->index(['file_id', 'product_id', 'inventory_updated', 'processed_at'], 'idx_inventory_processing');
            
            // Index for pricing imports
            $table->index(['file_id', 'product_id', 'pricing_imported', 'processed_at'], 'idx_pricing_processing');
            
            // Index for product creation
            $table->index(['file_id', 'product_id', 'sku', 'name', 'processed_at'], 'idx_product_creation');
        });
    }

    public function down(): void
    {
        Schema::table('modules_csv_product_imports', function (Blueprint $table) {
            $table->dropIndex('idx_file_processed');
            $table->dropIndex('idx_sku');
            $table->dropIndex('idx_product_processed');
            $table->dropIndex('idx_file_exists_processed');
            $table->dropIndex('idx_tags_add_processing');
            $table->dropIndex('idx_tags_remove_processing');
            $table->dropIndex('idx_alias_processing');
            $table->dropIndex('idx_inventory_processing');
            $table->dropIndex('idx_pricing_processing');
            $table->dropIndex('idx_product_creation');
        });
    }
};