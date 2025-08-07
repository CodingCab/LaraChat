<?php

namespace App\Modules\Maintenance\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class FixNullUnitPriceInInventoryMovementsJob extends UniqueJob
{
    public function handle(): void
    {
        $runs = 0;

        do {
            $recordsUpdated = retry(3, function () {
                return DB::update('
                    UPDATE `inventory_movements`
                    SET unit_price = 0,
                        unit_cost = IFNULL(unit_cost, 0)
                    WHERE unit_price IS NULL
                    LIMIT 500
                ');
            }, 100);

            $runs++;

            usleep(200000); // 0.2 second
        } while ($recordsUpdated > 0 && $runs < 100);
    }
}
