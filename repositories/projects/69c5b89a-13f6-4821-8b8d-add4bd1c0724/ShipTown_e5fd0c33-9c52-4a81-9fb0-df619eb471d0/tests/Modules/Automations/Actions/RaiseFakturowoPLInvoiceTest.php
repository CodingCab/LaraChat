<?php

namespace Tests\Modules\Automations\Actions;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Warehouse;
use App\Modules\Fakturowo\src\FakturowoServiceProvider;
use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use App\Modules\Fakturowo\src\OrderActions\RaiseFakturowoPLInvoice;
use App\User;
use Tests\TestCase;

class RaiseFakturowoPLInvoiceTest extends TestCase
{
    public function test_raise_fakturowo_pl_invoice(): void
    {
        if (empty(env('TEST_FAKTUROWO_PL_API_KEY'))) {
            $this->markTestSkipped('FakturowoPL API Key is not set in .env file');
        }

        FakturowoServiceProvider::enableModule();

        FakturowoConfiguration::query()->create([
            'connection_code' => 'fakturowo_1',
            'api_key' => env('TEST_FAKTUROWO_PL_API_KEY'),
        ]);

        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $address = OrderAddress::factory()->create([
            'country_code' => 'PL',
            'country_name' => 'Poland',
            'state_name' => 'Mazowieckie',
            'tax_id' => '3928621931',
            'address1' => 'Złota 59',
            'postcode' => '00-120',
            'city' => 'Warszawa',
        ]);
        $warehouse->address()->associate($address);
        $user->warehouse()->associate($warehouse);

        $order = Order::factory()->create([
            'status_code' => 'paid',
        ]);

        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        OrderProductShipment::create([
            'warehouse_id' => $warehouse->id,
            'order_id' => $orderProduct->order_id,
            'order_product_id' => $orderProduct->id,
            'product_id' => $orderProduct->product_id,
            'quantity_shipped' => 4,
        ]);

        $order->refresh();
        $action = new RaiseFakturowoPLInvoice($order, $user);

        $actionSucceeded = $action->handle('fakturowo_1');

        $this->assertTrue($actionSucceeded, 'Action failed');
    }
}
