<?php

namespace App\Modules\Inventory\src\Listeners;

use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;
use Illuminate\Support\Facades\DB;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        dispatch(function () {
            DB::statement('
                UPDATE inventory
                SET recount_required = 1
                WHERE id IN (
                    SELECT id FROM (
                        SELECT id
                        FROM inventory
                        WHERE
                            quantity != 0
                            AND recount_required = 0
                        ORDER BY
                            quantity, recalculated_at

                        LIMIT 200
                    ) as tbl
                )
            ');
        })->afterResponse();

        RecalculateInventoryRecordsJob::dispatch();
    }
}
