<?php

namespace Tests\Feature\Api\ShippingLabels;
use PHPUnit\Framework\Attributes\Test;

use App\Abstracts\ShippingServiceAbstract;
use App\Models\Order;
use App\Models\ShippingService;
use App\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TestShipmentService extends ShippingServiceAbstract
{
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        return collect();
    }
}

class StoreTest extends TestCase
{
    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $serviceCode = 'test_service_' . uniqid();

        ShippingService::factory()->create([
            'code' => $serviceCode,
            'service_provider_class' => TestShipmentService::class,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.shipping-labels.store'), [
                'shipping_service_code' => $serviceCode,
                'order_id' => $order->getKey(),
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}
