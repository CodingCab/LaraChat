<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class BarcodeScannedToQuantityFieldJob extends UniqueJob
{
    public function handle(): bool
    {
        $reason = 'possible barcode scanned into quantity field';
        $points = 100;

        $this->insertNewSuggestions($reason, $points);
        $this->deleteIncorrectSuggestions($reason);

        return true;
    }

    private function insertNewSuggestions(string $reason, int $points): void
    {
        DB::statement('
            INSERT INTO stocktake_suggestions (inventory_id, product_id, warehouse_id, points, reason, created_at, updated_at)
            SELECT id, product_id, warehouse_id, ?, ?, NOW(), NOW()
            FROM inventory
            WHERE quantity > 100000
                AND NOT EXISTS (
                    SELECT NULL
                    FROM stocktake_suggestions
                    WHERE stocktake_suggestions.inventory_id = inventory.id
                    AND stocktake_suggestions.reason = ?
                )
            ORDER BY quantity DESC
        ', [$points, $reason, $reason]);
    }

    private function deleteIncorrectSuggestions(string $reason): void
    {
        DB::statement('
            DELETE stocktake_suggestions
            FROM stocktake_suggestions
            LEFT JOIN inventory
                ON inventory.id = stocktake_suggestions.inventory_id

            WHERE stocktake_suggestions.reason = ?
            AND inventory.quantity < 100000
        ', [$reason]);
    }
}
