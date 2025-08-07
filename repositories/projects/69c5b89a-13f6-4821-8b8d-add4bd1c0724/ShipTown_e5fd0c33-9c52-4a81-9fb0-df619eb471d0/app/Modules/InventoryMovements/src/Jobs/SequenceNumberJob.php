<?php

namespace App\Modules\InventoryMovements\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SequenceNumberJob extends UniqueJob
{
    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->recreateRecalculateSequenceNumbersProcedure();

        $iterations = 0;
        $maxIterations = 500;

        do {
            retry(3, function () {
                DB::unprepared("CALL recalculate_sequence_numbers()");
            }, 3000, function ($e) {
                Log::error('Error executing recalculate_sequence_numbers procedure', [
                    'job' => self::class,
                    'error' => $e->getMessage(),
                ]);
            });

            Log::info('Job processing', [
                'job' => self::class,
                'iteration' => $iterations + 1,
            ]);

            usleep(100000); // 0.1 seconds
            $iterations++;
        } while ($iterations < $maxIterations && InventoryMovement::query()->whereNull('sequence_number')->exists());

        // Log if max iterations reached
        if ($iterations >= $maxIterations && InventoryMovement::query()->whereNull('sequence_number')->exists()) {
            Log::warning('Maximum iterations (100) reached but unprocessed movements remain', [
                'job' => self::class,
                'remaining' => InventoryMovement::query()->whereNull('sequence_number')->count(),
            ]);
        }
    }

    private function recreateRecalculateSequenceNumbersProcedure(): void
    {
        // Drop the procedure if it exists
        DB::unprepared("DROP PROCEDURE IF EXISTS recalculate_sequence_numbers");

        DB::unprepared("
            CREATE PROCEDURE recalculate_sequence_numbers()
            BEGIN
                # Drop existing temporary tables if they exist
                DROP TEMPORARY TABLE IF EXISTS tempInventoryMovementsToRecalculate;
                DROP TEMPORARY TABLE IF EXISTS tempRunningTotals;
                DROP TEMPORARY TABLE IF EXISTS tempNewValues;

                # Create first temp table with movements to recalculate
                CREATE TEMPORARY TABLE tempInventoryMovementsToRecalculate AS
                SELECT DISTINCT
                    im.inventory_id,
                    im.warehouse_code,

                    (SELECT occurred_at FROM inventory_movements as p
                     WHERE im.inventory_id = p.inventory_id
                     AND p.occurred_at <= im.occurred_at
                     AND p.id != im.id
                     ORDER BY occurred_at DESC, id DESC LIMIT 1) as from_occurred_at,

                    (SELECT sequence_number FROM inventory_movements as p
                     WHERE im.inventory_id = p.inventory_id
                     AND p.occurred_at <= im.occurred_at
                     AND p.id != im.id
                     AND p.sequence_number IS NOT NULL
                     ORDER BY occurred_at DESC, id DESC LIMIT 1) as last_sequence_number,

                    (SELECT quantity_after FROM inventory_movements as p
                     WHERE im.inventory_id = p.inventory_id
                     AND p.occurred_at <= im.occurred_at
                     AND p.id != im.id
                     AND p.sequence_number IS NOT NULL
                     ORDER BY occurred_at DESC, id DESC LIMIT 1) as last_quantity_after,

                    (SELECT occurred_at FROM inventory_movements as n
                     WHERE im.inventory_id = n.inventory_id
                     AND n.occurred_at >= im.occurred_at
                     AND n.type = 'stocktake'
                     AND n.sequence_number IS NULL
                     ORDER BY occurred_at ASC, id ASC LIMIT 1) as to_occurred_at

                FROM inventory_movements as im
                WHERE im.sequence_number IS NULL
                LIMIT 1;

                # SELECT * FROM tempInventoryMovementsToRecalculate;

                # Create running totals with window functions
                CREATE TEMPORARY TABLE tempRunningTotals AS
                SELECT
                    tempTable.inventory_id,
                    tempTable.warehouse_code,
                    im.occurred_at,
                    tempTable.from_occurred_at,
                    tempTable.to_occurred_at,
                    IFNULL(tempTable.last_sequence_number, 0) as last_sequence_number,
                    IFNULL(tempTable.last_quantity_after, 0) as last_quantity_after,
                    im.id as inventory_movement_id,
                    ROW_NUMBER() OVER w as sequence_number,
                    SUM(quantity_delta) OVER w as quantity_delta_sum
                FROM inventory_movements im
                INNER JOIN tempInventoryMovementsToRecalculate as tempTable
                    ON im.inventory_id = tempTable.inventory_id
                    AND im.occurred_at >= IFNULL(tempTable.from_occurred_at,im.occurred_at)
                    AND im.occurred_at <= IFNULL(tempTable.to_occurred_at, NOW())

                WHERE
                    im.sequence_number IS NULL
                    OR im.sequence_number > tempTable.last_sequence_number
                WINDOW w AS (
                    PARTITION BY im.inventory_id
                    ORDER BY im.occurred_at, im.id
                )
                ORDER BY im.inventory_id, im.occurred_at, im.id
                LIMIT 500;

                # SELECT * FROM tempRunningTotals;

                # Calculate new values
                CREATE TEMPORARY TABLE tempNewValues AS
                SELECT
                    i.product_sku,
                    i.id as inventory_id,
                    tempTable2.inventory_movement_id,
                    (tempTable2.last_sequence_number + tempTable2.sequence_number) AS new_sequence_number,
                    (IFNULL(tempTable2.last_quantity_after, 0) + quantity_delta_sum - im.quantity_delta) AS new_quantity_before,
                    CASE
                        WHEN im.type = 'stocktake'
                        THEN tempTable2.last_quantity_after + quantity_delta_sum - (IFNULL(tempTable2.last_quantity_after, 0) + quantity_delta_sum - im.quantity_delta)
                        ELSE im.quantity_delta
                    END as new_quantity_delta,
                    CASE
                        WHEN im.type = 'stocktake'
                        THEN im.quantity_after
                        ELSE IFNULL(tempTable2.last_quantity_after, 0) + quantity_delta_sum
                    END as new_quantity_after
                FROM tempRunningTotals tempTable2
                LEFT JOIN inventory_movements im ON im.id = tempTable2.inventory_movement_id
                LEFT JOIN inventory i ON i.id = im.inventory_id;

                # SELECT * FROM tempNewValues;

                # if sequence numbers already exist = set to null
                UPDATE inventory_movements im
                INNER JOIN tempNewValues
                    ON im.inventory_id = tempNewValues.inventory_id
                    AND im.sequence_number = tempNewValues.new_sequence_number
                SET
                    im.sequence_number = NULL
                WHERE im.sequence_number IS NOT NULL;

                # request recalculation of inventory
                UPDATE inventory
                SET recount_required = 1
                WHERE
                    id IN (SELECT DISTINCT inventory_id FROM tempNewValues);

                # Update the movements
                UPDATE inventory_movements im
                INNER JOIN tempNewValues ON im.id = tempNewValues.inventory_movement_id
                SET
                    im.sequence_number = tempNewValues.new_sequence_number,
                    im.quantity_before = tempNewValues.new_quantity_before,
                    im.quantity_delta = CASE WHEN im.type != 'stocktake' THEN im.quantity_delta
                                        ELSE tempNewValues.new_quantity_delta
                                        END,
                    im.quantity_after = CASE WHEN im.type = 'stocktake' THEN im.quantity_after
                                        ELSE tempNewValues.new_quantity_after
                                        END;
                # Fix quantity_delta for stocktake movements
                UPDATE inventory_movements im
                INNER JOIN tempNewValues ON im.id = tempNewValues.inventory_movement_id
                SET
                    im.quantity_delta = tempNewValues.new_quantity_after - tempNewValues.new_quantity_before
                WHERE im.type = 'stocktake';
            END
        ");
    }
}
