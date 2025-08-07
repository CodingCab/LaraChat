<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\OriginStatusCodeEqualsCondition;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OriginalStatusCodeEqualsConditionTest extends TestCase
{
    #[Test]
    public function test_condition_true(): void
    {
        Order::factory()->create(['origin_status_code' => 'processing']);

        $query = Order::query();
        OriginStatusCodeEqualsCondition::addQueryScope($query, 'processing');

        $this->assertEquals(1, $query->count());
    }

    #[Test]
    public function test_condition_false(): void
    {
        Order::factory()->create(['origin_status_code' => 'processing']);

        $query = Order::query();
        OriginStatusCodeEqualsCondition::addQueryScope($query, 'completed');

        $this->assertEquals(0, $query->count());
    }
}
