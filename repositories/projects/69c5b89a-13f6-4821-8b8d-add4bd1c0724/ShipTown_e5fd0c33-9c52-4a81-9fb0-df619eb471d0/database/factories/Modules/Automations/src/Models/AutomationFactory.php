<?php

namespace Database\Factories\Modules\Automations\src\Models;

use App\Modules\Automations\src\Models\Automation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationFactory extends Factory
{
    protected $model = Automation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
        ];
    }
}
