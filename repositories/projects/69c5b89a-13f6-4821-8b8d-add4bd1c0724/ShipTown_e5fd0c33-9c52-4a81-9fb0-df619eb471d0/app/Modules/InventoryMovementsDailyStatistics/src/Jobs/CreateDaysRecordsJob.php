<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDay;
use Illuminate\Support\Carbon;

class CreateDaysRecordsJob extends UniqueJob
{
    public function handle(): void
    {
        $lastMovementDayPresent = InventoryMovementsDay::query()->max('date');
        $firstMovementDatePresent = InventoryMovement::query()->min('occurred_at');

        $minDate = Carbon::parse($lastMovementDayPresent ?? $firstMovementDatePresent);

        self::insertDaysSinceFirstMovement($minDate, now());
    }

    public static function insertDaysSinceFirstMovement($from_date, $to_date): void
    {
        $datesToInsert = [];

        $fromDate = $from_date->clone()->startOfDay();

        do {
            $datesToInsert[] = [
                'date' => $fromDate->format('Y-m-d'),
                'recalc_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $fromDate = $fromDate->addDay();
        } while ($fromDate->lt(min($to_date, now())));

        if (!empty($datesToInsert)) {
            collect($datesToInsert)
                ->chunk(1000)
                ->each(function ($chunk) {
                    InventoryMovementsDay::query()
                        ->upsert($chunk->toArray(), [
                            'date',
                        ], [
                            'recalc_required' => true,
                            'max_inventory_id_checked' => 0,
                            'updated_at' => now(),
                        ]);
                });
        }
    }
}
