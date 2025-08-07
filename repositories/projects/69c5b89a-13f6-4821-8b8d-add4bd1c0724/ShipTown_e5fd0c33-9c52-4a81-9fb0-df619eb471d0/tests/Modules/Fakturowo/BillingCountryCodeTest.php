<?php

namespace Tests\Modules\Fakturowo;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Modules\Fakturowo\src\Services\FakturowoService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillingCountryCodeTest extends TestCase
{
    #[Test]
    public function it_converts_alpha3_country_code_to_alpha2(): void
    {
        $billingAddress = OrderAddress::factory()->create([
            'country_code' => 'ESP',
            'country_name' => 'Spain',
        ]);

        $order = Order::factory()->create([
            'billing_address_id' => $billingAddress->id,
            'total_shipping' => 0,
        ]);

        $orderProduct = OrderProduct::factory()->create([
            'order_id' => $order->id,
            'quantity_ordered' => 1,
            'quantity_shipped' => 1,
            'unit_sold_price' => 10,
            'unit_full_price' => 10,
            'unit_discount' => 0,
            'price' => 10,
        ]);

        $record = InvoiceOrderProduct::create([
            'order_id' => $order->id,
            'orders_products_id' => $orderProduct->id,
            'quantity_invoiced' => 1,
        ]);

        $data = FakturowoService::prepareInvoiceData($order, collect([$record]), false);

        $this->assertEquals('ES', $data['nabywca_kraj']);
    }
}
