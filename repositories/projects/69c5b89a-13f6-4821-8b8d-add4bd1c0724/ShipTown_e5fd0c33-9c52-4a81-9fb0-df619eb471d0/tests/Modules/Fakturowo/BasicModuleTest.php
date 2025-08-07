<?php

namespace Tests\Modules\Fakturowo;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Fakturowo\src\FakturowoServiceProvider;
use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Modules\Fakturowo\src\OrderActions\RaiseFakturowoPLInvoice;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function testBasicFunctionality()
    {
        if (!env('TEST_FAKTUROWO_API_KEY')) {
            $this->markTestSkipped('Fakturowo API key is not set in the environment variables.');
        }

        FakturowoServiceProvider::enableModule();

        FakturowoConfiguration::query()->create([
            'connection_code' => 'test-account',
            'api_key' => env('TEST_FAKTUROWO_API_KEY'),
        ]);

        /** @var Order $order */
        $order = Order::factory()->create();
        OrderProduct::factory()
            ->create([
                'order_id' => $order->id,
                'quantity_ordered' => 1,
                'quantity_shipped' => 1,
            ]);

        $action = new RaiseFakturowoPLInvoice($order);

        $result = $action->handle('test-account');

        $this->assertEquals(1, InvoiceOrderProduct::query()->count());
    }
}
