<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

/**
 * Class SyncCheckFailedProductsJob.
 */
class EnsureProductSkuIsFilledJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                WITH tempTable AS (
                    SELECT id
                    FROM `modules_magento2api_products`
                    WHERE sku IS NULL
                    LIMIT 500
                )

                UPDATE modules_magento2api_products

                INNER JOIN tempTable ON tempTable.id = modules_magento2api_products.id

                LEFT JOIN products
                  ON products.id = modules_magento2api_products.product_id

                SET modules_magento2api_products.sku = products.sku
            ');

            usleep(100000); // 100ms
        } while ($recordsAffected > 0);
    }
}
