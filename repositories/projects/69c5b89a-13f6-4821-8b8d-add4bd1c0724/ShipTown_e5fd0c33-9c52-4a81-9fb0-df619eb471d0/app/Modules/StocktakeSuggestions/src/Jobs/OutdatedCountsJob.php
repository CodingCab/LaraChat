<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration;
use Illuminate\Support\Facades\DB;

class OutdatedCountsJob extends UniqueJob
{
    private StocktakeSuggestionsConfiguration $config;

    public function handle(): bool
    {
        $this->config = StocktakeSuggestionsConfiguration::query()->firstOrCreate();

        $reason = 'outdated count';
        $points = 1;

        $this->addSuggestions($reason, $points);
        $this->removeSuggestions($reason);

        return true;
    }

    protected function addSuggestions(string $reason, int $points): bool
    {
        if (is_null($this->config->min_count_date)) {
            return true;
        }

        $minCountDate = $this->config->min_count_date->format('Y-m-d');
        DB::statement('
            INSERT INTO stocktake_suggestions (inventory_id, product_id, warehouse_id, points, reason, created_at, updated_at)
            SELECT inventory.id, inventory.product_id, inventory.warehouse_id, ? , ?, NOW(), NOW()
            FROM inventory
            LEFT JOIN stocktake_suggestions
                ON stocktake_suggestions.inventory_id = inventory.id
                AND stocktake_suggestions.reason = ?
            WHERE stocktake_suggestions.inventory_id IS NULL
                AND inventory.quantity != 0
                AND (inventory.in_stock_since IS NULL OR inventory.in_stock_since < ?)
                AND (inventory.last_counted_at IS NULL OR inventory.last_counted_at < ?)
        ', [$points, $reason, $reason, $minCountDate, $minCountDate]);

        return true;
    }

    private function removeSuggestions(string $reason): void
    {
        if (is_null($this->config->min_count_date)) {
            DB::statement('
                DELETE stocktake_suggestions
                FROM stocktake_suggestions
                WHERE stocktake_suggestions.reason = ?
            ', [$reason]);

            return;
        }

        $min_count_date = $this->config->min_count_date->format('Y-m-d');

        DB::statement('
            DELETE stocktake_suggestions
            FROM stocktake_suggestions
            INNER JOIN inventory
                ON inventory.id = stocktake_suggestions.inventory_id
            WHERE stocktake_suggestions.reason = ? AND (
                inventory.quantity = 0
                OR inventory.in_stock_since > ?
                OR inventory.last_counted_at > ?
            )
        ', [$reason, $min_count_date, $min_count_date]);
    }
}
