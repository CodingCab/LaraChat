<?php

namespace Tests\Feature\ShippingLabels\ShippingLabel;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/shipping-labels';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $order = Order::factory()->create();
        $shippingLabel = ShippingLabel::factory()->create([
            'order_id' => $order->getKey(),
            'shipping_number' => 'test',
        ]);
        $this->uri = route('shipping-labels', [$shippingLabel->getKey()]);
        $this->user = User::factory()->create();
    }

    #[Test]
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }

    #[Test]
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        ray($response);

        $response->assertOk();
    }

    #[Test]
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertOk();
    }
}
