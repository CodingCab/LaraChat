<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

/**
 * Class SyncCheckFailedProductsJob.
 */
class EnsureProductRecordsExistJob extends UniqueJob
{
    public function handle(): void
    {
        $tag = Tag::findOrCreate(['name' => 'Available Online']);

        DB::statement('
            INSERT INTO modules_magento2api_products (connection_id, sku, product_id, product_price_id, created_at, updated_at)
            SELECT
                modules_magento2api_connections.id as connection_id,
                products.sku,
                products_prices.product_id as product_id,
                products_prices.id as product_prices_id,
                now(),
                now()

            FROM taggables
            INNER JOIN modules_magento2api_connections ON 1=1
            LEFT JOIN products_prices
                ON products_prices.product_id = taggables.taggable_id
                AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id
            LEFT JOIN products
                ON products.id = products_prices.product_id
            LEFT JOIN modules_magento2api_products
                ON modules_magento2api_products.connection_id = modules_magento2api_connections.id
                AND modules_magento2api_products.product_price_id = products_prices.id

            WHERE taggables.tag_id = ?
                AND taggables.taggable_type = ?
                AND modules_magento2api_products.id IS NULL
        ', [$tag->first()->getKey(), Product::class]);
    }
}
