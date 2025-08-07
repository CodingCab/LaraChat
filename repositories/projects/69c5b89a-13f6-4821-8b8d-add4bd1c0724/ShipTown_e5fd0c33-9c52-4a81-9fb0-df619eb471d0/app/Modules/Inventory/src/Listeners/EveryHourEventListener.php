<?php

namespace App\Modules\Inventory\src\Listeners;

use Illuminate\Support\Facades\DB;

class EveryHourEventListener
{
    public function handle(): void
    {
        DB::statement('
            UPDATE inventory

            INNER JOIN inventory_movements
            ON inventory_movements.inventory_id = inventory.id
            AND inventory_movements.sequence_number > IFNULL(inventory.last_sequence_number, 0)

            SET inventory.recount_required = 1

            WHERE inventory.id IN (
                SELECT DISTINCT inventory_id
                FROM inventory_movements
                WHERE inventory_movements.created_at > DATE_SUB(now(), INTERVAL 2 HOUR)
            )
        ');
    }
}
