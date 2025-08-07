<?php

namespace App\Modules\Maintenance\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class FillUnitSoldPriceInOrdersProductsTableJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsUpdated = DB::update('
                UPDATE orders_products
                SET unit_sold_price = price
                WHERE unit_sold_price = 0
                  AND price > 0
                LIMIT 1000
            ');

            usleep(100000); // 0.1 second
        } while ($recordsUpdated > 0);
    }
}
