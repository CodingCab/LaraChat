<?php

namespace App\Modules\Maintenance\src\Jobs\Products;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class EnsureAllProductPriceRecordsExistsJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                INSERT INTO products_prices
                (
                    product_id,
                    warehouse_id,
                    warehouse_code,
                    price,
                    sale_price,
                    sale_price_start_date,
                    sale_price_end_date,
                    created_at,
                    updated_at
                )
                SELECT
                    products.id,
                    warehouses.id,
                    warehouses.code,
                    price,
                    sale_price,
                    sale_price_start_date,
                    sale_price_end_date,
                    now(),
                    now()

                FROM products
                LEFT JOIN warehouses ON 1 = 1
                WHERE NOT EXISTS (
                    SELECT product_id FROM products_prices
                    WHERE products_prices.warehouse_id = warehouses.id AND products_prices.product_id = products.id
                )
                LIMIT 1000
            ');

            usleep(200000); // 200ms
        } while ($recordsAffected > 0);
    }
}
