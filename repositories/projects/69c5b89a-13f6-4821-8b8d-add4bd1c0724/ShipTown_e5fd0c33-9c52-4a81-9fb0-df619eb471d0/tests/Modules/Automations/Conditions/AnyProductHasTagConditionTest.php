<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductTotal;
use App\Modules\Automations\src\Conditions\Order\AnyProductHasTagCondition;
use Tests\TestCase;

class AnyProductHasTagConditionTest extends TestCase
{
    public function test_functionality(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();

        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);
        $orderProduct->product->attachTag('oversize');

        ray($order->toArray());
        ray($orderProduct->toArray());

        $query = Order::query();
        AnyProductHasTagCondition::addQueryScope($query, 'oversize');
        $this->assertCount(1, $query->get(), 'Order contains at least one product with "oversize" tag.');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());
        ray($query->toSql(), $query->getBindings());
        ray($query->get()->toArray());
    }
}
