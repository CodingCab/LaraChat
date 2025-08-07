<?php

namespace Tests\External\DpdUk;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\ShippingService;
use App\Modules\DpdUk\src\Models\Connection;
use App\Modules\DpdUk\src\Services\DpdUkService;
use App\Modules\DpdUk\src\Services\NextDayShippingService;
use Carbon\Carbon;
use Tests\TestCase;

class GenerateLabelDocumentJobTest extends TestCase
{
    private string $testSkippingExpiryDate = '01 June 2022';

    /**
     * @throws \Exception
     */
    public function test_print_new_label(): void
    {
        if (Carbon::make($this->testSkippingExpiryDate)->isFuture()) {
            $this->markTestSkipped();
        }

        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'My';
        $testAddress->last_name = 'Contact';
        $testAddress->phone = '0121 500 2500';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'GB';
        $testAddress->postcode = 'B66 1BY';
        $testAddress->address1 = 'Roebuck Lane';
        $testAddress->address2 = 'Smethwick';
        $testAddress->city = 'Birmingham';
        $testAddress->state_code = 'West Midlands';
        $testAddress->save();

        /** @var Connection $connection */
        $connection = Connection::factory()->make();
        $connection->collection_address_id = $testAddress->getKey();
        $connection->save();

        $order = Order::factory()->create();
        $shippingService = ShippingService::query()
            ->where('service_provider_class', NextDayShippingService::class)
            ->first();
        $shipment = (new NextDayShippingService())->ship($order->getKey(), $shippingService);

        $this->assertGreaterThan(0, $shipment->count());
    }
}
