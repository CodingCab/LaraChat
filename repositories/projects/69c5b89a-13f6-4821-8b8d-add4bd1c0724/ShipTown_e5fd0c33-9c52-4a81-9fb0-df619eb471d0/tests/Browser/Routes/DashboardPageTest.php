<?php

namespace Tests\Browser\Routes;

use App\Models\Configuration;
use App\Models\Order;
use App\Models\OrderProduct;
use Tests\DuskTestCase;

class DashboardPageTest extends DuskTestCase
{
    private string $uri = '/dashboard';

    public function testBasicScenario(): void
    {
        Configuration::query()->update(['ecommerce_connected' => true]);

        $this->visit($this->uri);

        $order = Order::factory()->create(['status_code' => 'paid']);

        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $this->screenshot();

        $this->pause(5);

        $this->browser()
            ->assertSee('Orders - Packed')
            ->assertSee('Orders - Active')
            ->assertSee('Active Orders By Age');
    }
}
