<?php

namespace Tests\Feature\Api\OrdersStatuses\OrdersStatus;
use PHPUnit\Framework\Attributes\Test;

use App\Models\OrderStatus;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        $orderStatus = OrderStatus::create([
            'name' => 'testing',
            'code' => 'testing',
            'order_active' => 0,
            'sync_ecommerce' => 0,
        ]);

        $response = $this->delete(route('api.orders-statuses.destroy', $orderStatus));
        $response->assertOk();
    }

    public function test_cannot_delete_order_active(): void
    {
        $orderStatus = OrderStatus::create([
            'name' => 'testing',
            'code' => 'testing',
            'order_active' => 1,
            'sync_ecommerce' => 0,
        ]);

        $response = $this->delete(route('api.orders-statuses.destroy', $orderStatus));
        $response->assertStatus(401);
    }

    public function test_cannot_delete_sync_ecommerce(): void
    {
        $orderStatus = OrderStatus::create([
            'name' => 'testing',
            'code' => 'testing',
            'order_active' => 0,
            'sync_ecommerce' => 1,
        ]);

        $response = $this->delete(route('api.orders-statuses.destroy', $orderStatus));
        $response->assertStatus(401);
    }
}
