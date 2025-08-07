<?php

namespace Tests\Feature\Api\Packlist\Order;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        OrderStatus::factory()->create([
            'code' => 'packing',
            'name' => 'packing',
            'order_active' => true,
            'order_on_hold' => false,
        ]);

        $warehouse = Warehouse::factory()->create();

        $order = Order::factory()->create(['status_code' => 'packing']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var User $user */
        $user = User::factory()->create([
            'location_id' => $warehouse->getKey(),
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson(route('api.packlist.order.index', [
                'filter[inventory_source_warehouse_id]' => $user->location_id,
            ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                ],
            ],
        ]);
    }

    #[Test]
    public function test_index_call_returns_422(): void
    {
        $user = User::factory()->create();

        OrderStatus::factory()->create([
            'code' => 'packing',
            'name' => 'packing',
            'order_active' => true,
            'order_on_hold' => false,
        ]);

        Order::factory()->create(['status_code' => 'packing']);

        $response = $this->actingAs($user, 'api')->getJson(route('api.packlist.order.index'));

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'errors' => [
                'filter' => [],
            ],
        ]);
    }
}
