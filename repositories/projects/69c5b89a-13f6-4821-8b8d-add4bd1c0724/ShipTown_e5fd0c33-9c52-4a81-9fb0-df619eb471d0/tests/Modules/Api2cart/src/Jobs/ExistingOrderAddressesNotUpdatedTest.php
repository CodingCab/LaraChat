<?php

namespace Tests\Modules\Api2cart\src\Jobs;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Database\Factories\Modules\Api2cart\src\Models\Api2cartOrderImportsFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExistingOrderAddressesNotUpdatedTest extends TestCase
{
    #[Test]
    public function testAddressesDoNotUpdateWhenOrderExists(): void
    {
        Order::query()->forceDelete();
        OrderAddress::query()->forceDelete();
        Api2cartOrderImports::query()->forceDelete();

        $rawImport = Api2cartOrderImportsFactory::new()->getDefaultRawImport();
        $import = Api2cartOrderImports::factory()->create([
            'raw_import' => $rawImport,
        ]);

        ProcessImportedOrdersJob::dispatch();

        $order = Order::query()
            ->with(['shippingAddress', 'billingAddress'])
            ->where('order_number', $import->order_number)
            ->firstOrFail();

        $originalShipping = $order->shippingAddress->first_name;
        $originalBilling = $order->billingAddress->first_name;

        $rawImport['shipping_address']['first_name'] = 'Changed';
        $rawImport['billing_address']['first_name'] = 'ChangedB';

        Api2cartOrderImports::factory()->create([
            'raw_import' => $rawImport,
        ]);

        ProcessImportedOrdersJob::dispatch();

        $order->refresh();

        $this->assertEquals($originalShipping, $order->shippingAddress->first_name);
        $this->assertEquals($originalBilling, $order->billingAddress->first_name);
    }
}
