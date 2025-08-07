<?php

namespace Tests\Feature\Pdf\Orders\OrderNumber\Template;

use App\Models\Order;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressLabel2ResponsiveTest extends TestCase
{
    #[Test]
    public function it_contains_responsive_styles(): void
    {
        $order = Order::factory()->create();
        $order->load(['shippingAddress', 'billingAddress']);

        $html = view()->make('pdf/orders/address_label2', $order->toArray())->render();

        $this->assertStringContainsString('.label {', $html);
        $this->assertStringContainsString('max-width: 100%', $html);
    }
}
