<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class NoMovementJob extends UniqueJob
{
    private string $reason;

    private int $points;

    public function handle()
    {
        $this->reason = 'quantity>100, price>5 and no movement for 7 days, scan product to verify barcode and stock';
        $this->points = 3;

        $this->insertNewStocktakeSuggestions();
        $this->deleteIncorrectSuggestions();
    }

    private function insertNewStocktakeSuggestions(): void
    {
        DB::statement("
            INSERT INTO stocktake_suggestions (inventory_id, product_id, warehouse_id, points, reason, created_at, updated_at)
            SELECT inventory.id, inventory.product_id, inventory.warehouse_id, ? , ?, NOW(), NOW()
            FROM `inventory`

            LEFT JOIN products_prices
                ON products_prices.product_id = inventory.product_id
                AND products_prices.warehouse_id = inventory.warehouse_id

            WHERE inventory.quantity > 0
            AND inventory.quantity_available > 100
            AND products_prices.price > 5
            AND inventory.last_sold_at < '".now()->subDays(7)->format('Y-m-d H:i:s')."'
            AND inventory.last_movement_at > inventory.last_counted_at
            AND NOT EXISTS (
                SELECT NULL
                FROM stocktake_suggestions
                WHERE stocktake_suggestions.inventory_id = inventory.id
                AND stocktake_suggestions.reason = ?
            )
        ", [$this->points, $this->reason, $this->reason]);
    }

    private function deleteIncorrectSuggestions(): void
    {
        DB::statement("
            DELETE stocktake_suggestions
            FROM stocktake_suggestions
            INNER JOIN inventory
                ON inventory.id = stocktake_suggestions.inventory_id

            INNER JOIN products_prices
                ON products_prices.product_id = inventory.product_id
                AND products_prices.warehouse_id = inventory.warehouse_id

            WHERE stocktake_suggestions.reason = ? AND (
                inventory.quantity_available < 100
                OR products_prices.price < 5
                OR inventory.last_sold_at > '".now()->subDays(7)->format('Y-m-d H:i:s')."'
                OR inventory.last_movement_at < inventory.last_counted_at
            )
        ", [$this->reason]);
    }
}
