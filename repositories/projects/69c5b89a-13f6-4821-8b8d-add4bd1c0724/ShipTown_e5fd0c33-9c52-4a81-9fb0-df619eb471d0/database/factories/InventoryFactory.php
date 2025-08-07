<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'product_sku' => $this->faker->word(),
            'warehouse_id' => null,
            'product_id' => null,
            'warehouse_code' => implode('', [
                $this->faker->randomLetter(),
                $this->faker->randomLetter(),
                $this->faker->randomLetter(),
                $this->faker->randomLetter(),
            ]),
            'shelve_location' => Str::upper($this->faker->randomLetter . $this->faker->randomNumber(2)),
            'quantity' => $this->faker->randomFloat(2, 0, 100),
            'quantity_reserved' => $this->faker->randomFloat(2, 0, 100),
            'quantity_incoming' => $this->faker->randomFloat(2, 0, 100),
            'restock_level' => $this->faker->randomFloat(2, 0, 100),
            'reorder_point' => $this->faker->randomFloat(2, 0, 100),
            'last_movement_id' => null,
            'first_movement_at' => null,
            'last_movement_at' => null,
            'first_received_at' => null,
            'last_received_at' => null,
            'first_sold_at' => null,
            'last_sold_at' => null,
            'first_counted_at' => null,
            'last_counted_at' => null,
            'in_stock_since' => null,
        ];
    }
}
