<?php

namespace App\Modules\Maintenance\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class InventoryTableCopyJob extends UniqueJob
{
    public function handle()
    {
        do {
            $recordsUpdated = DB::update('
                INSERT INTO inventory_movements_new (
                    id,
                    is_first_movement,
                    type,
                    custom_unique_reference_id,
                    inventory_id,
                    product_id,
                    warehouse_id,
                    quantity_delta,
                    quantity_before,
                    quantity_after,
                    description,
                    user_id,
                    created_at,
                    updated_at
                )
                SELECT
                    inventory_movements_copy.id,
                    inventory_movements_copy.is_first_movement,
                    inventory_movements_copy.type,
                    inventory_movements_copy.custom_unique_reference_id,
                    inventory_movements_copy.inventory_id,
                    inventory_movements_copy.product_id,
                    inventory_movements_copy.warehouse_id,
                    inventory_movements_copy.quantity_delta,
                    inventory_movements_copy.quantity_before,
                    inventory_movements_copy.quantity_after,
                    inventory_movements_copy.description,
                    inventory_movements_copy.user_id,
                    inventory_movements_copy.created_at,
                    inventory_movements_copy.updated_at

                FROM `inventory_movements_copy`
                LEFT JOIN inventory_movements
                    ON inventory_movements.id = inventory_movements_copy.id

                WHERE isnull(inventory_movements.id)
                LIMIT 5000
            ');
            sleep(1);
        } while ($recordsUpdated > 0);
    }
}
