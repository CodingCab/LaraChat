<?php

namespace Database\Factories\Modules\InventoryMovementsDailyStatistics\src\Models;

use App\Models\InventoryMovement;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementsDailyStatisticFactory extends Factory
{
    protected $model = InventoryMovementsDailyStatistic::class;

    public function definition(): array
    {
        /** @var InventoryMovement $im */
        $im = InventoryMovement::factory()->create();

        return [
            'recalc_required' => $this->faker->boolean,
            'date' => $im->occurred_at->toDateString(),
            'warehouse_code' => $im->warehouse_code,
            'inventory_id' => $im->inventory_id,
            'last_inventory_movement_id' => $im->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
