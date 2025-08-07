<?php

namespace Tests\Modules\Api2cart\src\Jobs;

use App\Models\Order;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Database\Factories\Modules\Api2cart\src\Models\Api2cartOrderImportsFactory;
use Tests\TestCase;

class ProcessImportedRecordJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Api2cartConnection::factory()->create();
    }

    public function test(): void
    {
        /** @var Api2cartOrderImports $orderImport */
        $orderImport = Api2cartOrderImports::factory()->create([
            'raw_import' => Api2cartOrderImportsFactory::getInPostRawImport()
        ]);

        ProcessImportedOrdersJob::dispatch();

        $order = Order::query()->with(['shippingAddress', 'billingAddress', 'payments'])
            ->where('order_number', $orderImport->order_number)
            ->first();

        ray($orderImport, $order);

        $this->assertNotEmpty($order, 'Order was not created');
        $this->assertEquals($orderImport->order_number, $order->order_number, 'Order number does not match');
        $this->assertNotEmpty($orderImport->refresh()->when_processed, 'Order was not processed');

        // shipping address
        $this->assertEquals($order->shippingAddress->email, $orderImport->raw_import['customer']['email']);
        $this->assertEquals($order->shippingAddress->first_name, $orderImport->raw_import['shipping_address']['first_name']);
        $this->assertEquals($order->shippingAddress->last_name, $orderImport->raw_import['shipping_address']['last_name']);
        $this->assertEquals($order->shippingAddress->address1, $orderImport->raw_import['shipping_address']['address1']);
        $this->assertEquals($order->shippingAddress->address2, $orderImport->raw_import['shipping_address']['address2']);

        // billing address
        $this->assertEquals($order->billingAddress->first_name, $orderImport->raw_import['billing_address']['first_name']);
        $this->assertEquals($order->billingAddress->last_name, $orderImport->raw_import['billing_address']['last_name']);
        $this->assertEquals($order->billingAddress->address1, $orderImport->raw_import['billing_address']['address1']);
        $this->assertEquals($order->billingAddress->address2, $orderImport->raw_import['billing_address']['address2']);

        // payments
//        $payment = $orderImport->extractPaymentAttributes()[0];
//        $orderPayment = $order->payments->first();
//        $this->assertEquals($orderPayment->amount, $payment['amount']);
//        $this->assertEquals($orderPayment->name, $payment['name']);
//        $this->assertEquals($orderPayment->additional_fields, $payment['additional_fields']);
    }

    public function testInPostLockerBoxCode(): void
    {
        /** @var Api2cartOrderImports $orderImport */
        $orderImport = Api2cartOrderImports::factory()->create([
            'raw_import' => Api2cartOrderImportsFactory::getInPostRawImport()
        ]);

        ProcessImportedOrdersJob::dispatch();

        $order = Order::query()->with(['shippingAddress'])
            ->where('order_number', $orderImport->order_number)
            ->first();

        $this->assertEquals($order->shippingAddress->locker_box_code, $orderImport->raw_import['additional_fields']['smpaczkomaty']['code']);
    }

    public function testDpdLockerBoxCode(): void
    {
        /** @var Api2cartOrderImports $orderImport */
        $orderImport = Api2cartOrderImports::factory()->create([
            'raw_import' => Api2cartOrderImportsFactory::getDpdRawImport()
        ]);

        ProcessImportedOrdersJob::dispatch();

        $order = Order::query()->with(['shippingAddress'])
            ->where('order_number', $orderImport->order_number)
            ->first();

        $this->assertEquals($order->shippingAddress->locker_box_code, $orderImport->raw_import['additional_fields']['dpd_code']);
    }
}
