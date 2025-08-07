<?php

namespace Tests\Feature\Pdf\Orders\OrderNumber\Template;

use App\Models\Order;
use App\Services\OrderService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressLabel2ParagonNoticeTest extends TestCase
{
    #[Test]
    public function it_adds_receipt_notice_when_tax_id_missing(): void
    {
        $order = Order::factory()->create();

        $order->load(['shippingAddress', 'billingAddress']);

        $html = view()->make('pdf/orders/address_label2', $order->toArray())->render();

        $this->assertStringContainsString('Issue Receipt', $html);
    }

    #[Test]
    public function it_hides_receipt_notice_when_tax_id_present(): void
    {
        $order = Order::factory()->create();

        $order->billingAddress->tax_id = '123456';
        $order->billingAddress->save();

        $order->load(['shippingAddress', 'billingAddress']);

        $html = view()->make('pdf/orders/address_label2', $order->toArray())->render();

        $this->assertStringNotContainsString('Issue Receipt', $html);
    }
}
