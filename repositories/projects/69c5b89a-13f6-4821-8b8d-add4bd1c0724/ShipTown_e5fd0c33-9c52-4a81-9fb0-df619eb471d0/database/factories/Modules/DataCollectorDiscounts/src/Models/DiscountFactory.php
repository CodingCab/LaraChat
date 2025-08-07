<?php

namespace Database\Factories\Modules\DataCollectorDiscounts\src\Models;

use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word,
            'percentage_discount' => $this->faker->numberBetween(1, 100),
        ];
    }
}
