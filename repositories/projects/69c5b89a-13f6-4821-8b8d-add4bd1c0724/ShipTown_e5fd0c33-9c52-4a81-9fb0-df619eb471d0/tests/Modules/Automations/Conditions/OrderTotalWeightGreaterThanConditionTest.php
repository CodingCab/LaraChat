<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\OrderTotalWeightGreaterThanCondition;
use Database\Seeders\OrderWithWeightSeeder;
use Tests\TestCase;

class OrderTotalWeightGreaterThanConditionTest extends TestCase
{
    public function test_functionality(): void
    {
        $this->seed(OrderWithWeightSeeder::class);

        $query = Order::query();
        OrderTotalWeightGreaterThanCondition::addQueryScope($query, 30);
        $this->assertCount(3, $query->get());
    }
}
