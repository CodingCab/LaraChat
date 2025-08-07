<?php

namespace Tests\Models\Orders;
use PHPUnit\Framework\Attributes\Test;

use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use Tests\TestCase;

class AgeInDaysAttributeTest extends TestCase
{
    #[Test]
    public function testExample()
    {
        // 23:59:59 today vs 22:59:59 today
        $this->travelTo(now()->ceilDay()->subSecond());
        $order = Order::factory()->create(['order_placed_at' => now()->subHour()]);
        $this->assertEquals(0, $order->refresh()->age_in_days);

        // 23:59:59 today vs 00:00:01 today
        $this->travelTo(now()->ceilDay()->subSecond());
        $order = Order::factory()->create(['order_placed_at' => now()->floorDay()->addSecond()]);
        $this->assertEquals(0, $order->refresh()->age_in_days);

        // 23:59:59 today vs 23:59:59 yesterday
        $this->travelTo(now()->ceilDay()->subSecond());
        $order = Order::factory()->create(['order_placed_at' => now()->subHours(24)]);
        $this->assertEquals(1, $order->refresh()->age_in_days);

        // 23:00:00 today vs 23:00:01 yesterday
        $this->travelTo(now()->ceilDay()->subHour());
        $order = Order::factory()->create(['order_placed_at' => now()->subHours(24)->addSecond()]);
        $this->assertEquals($order->refresh()->age_in_days, 1);

        // 23:00:00 today vs 23:59:59 yesterday
        $this->travelTo(now()->ceilDay()->subHour());
        $order = Order::factory()->create(['order_placed_at' => now()->floorDay()->subSecond()]);
        $this->assertEquals($order->refresh()->age_in_days, 1);

        // 00:00:01 today vs 23:59:59 yesterday
        $this->travelTo(now()->floorDay()->addSecond());
        $order = Order::factory()->create(['order_placed_at' => now()->floorDay()->subSecond()]);
        $this->assertEquals($order->refresh()->age_in_days, 1);

        // 00:00:01 yesterday vs 23:59:59 today
        $this->travelTo(now()->floorDay()->subSecond());
        $order = Order::factory()->create(['order_placed_at' => now()->ceilDay()->subDay()->subSecond()]);
        $this->assertEquals($order->refresh()->age_in_days, 1);
    }
}
