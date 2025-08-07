<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class NegativeInventoryJob extends UniqueJob
{
    public function handle(): bool
    {
        $reason = 'negative stock - have you received in the stock correctly?';
        $points = 5;

        $this->insertNewSuggestions($reason, $points);
        $this->deleteIncorrectSuggestions($reason);

        return true;
    }

    private function insertNewSuggestions(string $reason, int $points): void
    {
        DB::statement('
            INSERT INTO stocktake_suggestions (inventory_id, product_id, warehouse_id, points, reason, created_at, updated_at)
            SELECT id, product_id, warehouse_id, 5, "negative stock - have you received in the stock correctly?", NOW(), NOW()
            FROM inventory
            WHERE quantity < 0
                AND NOT EXISTS (
                    SELECT NULL
                    FROM stocktake_suggestions
                    WHERE stocktake_suggestions.inventory_id = inventory.id
                    AND stocktake_suggestions.reason = "negative stock - have you received in the stock correctly?"
                )
        ');
    }

    private function deleteIncorrectSuggestions(string $reason): void
    {
        DB::statement('
            DELETE stocktake_suggestions
            FROM stocktake_suggestions
            LEFT JOIN inventory
                ON inventory.id = stocktake_suggestions.inventory_id

            WHERE stocktake_suggestions.reason = "negative stock - have you received in the stock correctly?"
            AND inventory.quantity >= 0
        ');
    }
}
