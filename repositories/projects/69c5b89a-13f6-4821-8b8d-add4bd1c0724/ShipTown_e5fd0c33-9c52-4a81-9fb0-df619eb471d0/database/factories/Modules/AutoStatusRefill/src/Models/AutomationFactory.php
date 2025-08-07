<?php

namespace Database\Factories\Modules\AutoStatusRefill\src\Models;

use App\Modules\AutoStatusRefill\src\Models\Automation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationFactory extends Factory
{
    protected $model = Automation::class;

    public function definition(): array
    {
        return [
            'from_status_code' => $this->faker->word(),
            'to_status_code' => $this->faker->word(),
            'desired_order_count' => $this->faker->numberBetween(1, 100),
            'refill_only_at_0' => $this->faker->boolean(),
        ];
    }
}
