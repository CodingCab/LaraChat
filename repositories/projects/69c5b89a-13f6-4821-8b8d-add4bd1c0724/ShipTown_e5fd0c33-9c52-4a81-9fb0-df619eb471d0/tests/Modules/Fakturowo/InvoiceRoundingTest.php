<?php

namespace Tests\Modules\Fakturowo;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Modules\Fakturowo\src\Services\FakturowoService;
use App\Modules\OrderTotals\src\Services\OrderTotalsService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceRoundingTest extends TestCase
{
    #[Test]
    public function product_totals_are_rounded_to_two_decimals(): void
    {
        $order = Order::factory()->create([
            'total_shipping' => 0,
        ]);

        $orderProduct = OrderProduct::factory()->create([
            'order_id' => $order->id,
            'quantity_ordered' => 3,
            'quantity_shipped' => 3,
            'unit_sold_price' => 2.333,
            'unit_full_price' => 2.333,
            'unit_discount' => 0,
            'price' => 2.333,
        ]);

        OrderTotalsService::updateTotals($order->id);
        $order->refresh();

        $record = InvoiceOrderProduct::create([
            'order_id' => $order->id,
            'orders_products_id' => $orderProduct->id,
            'quantity_invoiced' => 3,
        ]);

        $data = FakturowoService::prepareInvoiceData($order, collect([$record]), false);

        $this->assertEquals(7.00, $data['produkt_wartosc_brutto_1']);
        $this->assertEquals(7.00, $data['dokument_zaplacono']);
    }
}
