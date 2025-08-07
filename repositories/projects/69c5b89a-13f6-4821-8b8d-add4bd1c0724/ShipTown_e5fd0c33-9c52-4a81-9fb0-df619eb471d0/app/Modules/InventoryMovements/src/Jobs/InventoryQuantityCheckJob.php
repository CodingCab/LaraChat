<?php

namespace App\Modules\InventoryMovements\src\Jobs;

use App\Abstracts\UniqueJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryQuantityCheckJob extends UniqueJob
{
    private Carbon $date;

    public function __construct($date = null)
    {
        $this->date = $date ?? Carbon::now();
    }

    public function handle()
    {
        $maxRounds = 500;

        do {
            $recordsUpdated = DB::update('
                UPDATE inventory_movements
                INNER JOIN inventory
                    ON inventory_movements.inventory_id = inventory.id
                    AND inventory_movements.sequence_number = inventory.last_sequence_number

                SET sequence_number = null

                WHERE inventory_movements.occurred_at BETWEEN ? AND ?
                    AND inventory_movements.sequence_number IS NOT NULL
                    AND (
                           inventory.quantity != inventory_movements.quantity_after
                        OR inventory.last_movement_id != inventory_movements.id
                        OR inventory.last_movement_at != inventory_movements.occurred_at
                    )
            ', [$this->date->startOfDay()->toDateTimeLocalString(), $this->date->endOfDay()->toDateTimeLocalString()]);

            Log::info('Job processing', [
                'job' => self::class,
                'recordsUpdated' => $recordsUpdated,
            ]);

            usleep(100000); // 0.1 seconds
            $maxRounds--;
        } while ($recordsUpdated > 0 and $maxRounds > 0);
    }
}
