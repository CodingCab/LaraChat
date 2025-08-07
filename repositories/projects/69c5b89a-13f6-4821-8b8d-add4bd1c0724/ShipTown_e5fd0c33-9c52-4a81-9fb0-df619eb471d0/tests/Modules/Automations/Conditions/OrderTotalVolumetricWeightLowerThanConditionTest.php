<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\OrderTotalVolumetricWeightLowerThanCondition;
use Database\Seeders\OrderWithWeightSeeder;
use Tests\TestCase;

class OrderTotalVolumetricWeightLowerThanConditionTest extends TestCase
{
    public function test_functionality(): void
    {
        $this->seed(OrderWithWeightSeeder::class);

        $query = Order::query();
        OrderTotalVolumetricWeightLowerThanCondition::addQueryScope($query, 10000);
        $this->assertCount(3, $query->get());
    }
}
