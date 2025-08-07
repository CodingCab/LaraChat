<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

/**
 * Class SyncCheckFailedProductsJob.
 */
class EnsureProductPriceIdIsFilledJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                WITH tempTable AS (
                    SELECT id
                    FROM `modules_magento2api_products`
                    WHERE product_price_id IS NULL
                    LIMIT 500
                )

                UPDATE modules_magento2api_products

                INNER JOIN tempTable ON tempTable.id = modules_magento2api_products.id

                LEFT JOIN modules_magento2api_connections
                  ON modules_magento2api_products.connection_id = modules_magento2api_connections.id
                LEFT JOIN products_prices
                  ON products_prices.product_id = modules_magento2api_products.product_id
                  AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id

                SET modules_magento2api_products.product_price_id = products_prices.id
            ');

            usleep(100000); // 100ms
        } while ($recordsAffected > 0);
    }
}
