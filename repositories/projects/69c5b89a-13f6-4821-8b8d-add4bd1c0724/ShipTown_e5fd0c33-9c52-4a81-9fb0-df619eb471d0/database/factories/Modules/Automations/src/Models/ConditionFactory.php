<?php

namespace Database\Factories\Modules\Automations\src\Models;

use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition(): array
    {
        return [
            'automation_id' => Automation::factory()->create()->getKey(),
        ];
    }
}
