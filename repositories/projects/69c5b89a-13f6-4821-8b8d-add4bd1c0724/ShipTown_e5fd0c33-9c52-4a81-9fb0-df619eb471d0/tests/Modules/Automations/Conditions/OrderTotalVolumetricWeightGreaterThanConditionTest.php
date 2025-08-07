<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\OrderTotalVolumetricWeightGreaterThanCondition;
use Database\Seeders\OrderWithWeightSeeder;
use Tests\TestCase;

class OrderTotalVolumetricWeightGreaterThanConditionTest extends TestCase
{
    public function test_functionality(): void
    {
        $this->seed(OrderWithWeightSeeder::class);

        $query = Order::query();
        OrderTotalVolumetricWeightGreaterThanCondition::addQueryScope($query, 10000);
        $this->assertCount(3, $query->get());
    }
}
