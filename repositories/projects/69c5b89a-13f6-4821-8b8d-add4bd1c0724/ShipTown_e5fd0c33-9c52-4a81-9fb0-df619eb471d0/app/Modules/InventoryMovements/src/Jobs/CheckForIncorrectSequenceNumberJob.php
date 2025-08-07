<?php

namespace App\Modules\InventoryMovements\src\Jobs;

use App\Abstracts\UniqueJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckForIncorrectSequenceNumberJob extends UniqueJob
{
    private Carbon $date;

    public function __construct($date = null)
    {
        $this->date = $date ?? Carbon::now();
    }

    public function handle(): void
    {
        do {
            DB::statement('DROP TEMPORARY TABLE IF EXISTS movementsWithIncorrectQuantityBefore;');

            DB::statement('
                CREATE TEMPORARY TABLE movementsWithIncorrectQuantityBefore AS
                    SELECT
                        previous_movement.id as previous_movement_id,
                        inventory_movements.*

                         FROM inventory_movements

                    LEFT JOIN inventory_movements as previous_movement
                        ON inventory_movements.inventory_id = previous_movement.inventory_id
                        AND inventory_movements.sequence_number - 1 = previous_movement.sequence_number

                    WHERE inventory_movements.occurred_at BETWEEN ? AND ?
                    AND inventory_movements.sequence_number > 1
                    AND (
                      previous_movement.id IS NULL
                      OR inventory_movements.quantity_before != previous_movement.quantity_after
                    )

                    LIMIT 10000;
            ', [$this->date->toDateTimeLocalString(), $this->date->addDay()->toDateTimeLocalString()]);

            $recordsUpdated = DB::update('
                UPDATE inventory_movements
                SET sequence_number = null
                WHERE id IN (SELECT id FROM movementsWithIncorrectQuantityBefore);
            ');

            Log::info('Job processing', [
                'job' => self::class,
                'recordsUpdated' => $recordsUpdated,
            ]);

            usleep(100000); // 0.1 seconds
        } while ($recordsUpdated > 0);
    }
}
