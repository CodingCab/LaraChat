<?php

namespace Tests\Modules\Couriers;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Illuminate\Support\Arr;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function testParamsArrayMerge()
    {
        /** @var Order $order */
        $order = Order::factory()->create();

        $collectionAddress = OrderAddress::factory()->create();

        $payload = ShippyProApi::convertToDpdPolandFormat($order, $collectionAddress, [
            'Params' => [
                'CarrierService' => 'CashOnDelivery',
                'CashOnDelivery' => 1,
                'CashOnDeliveryCurrency' => 'PLN',
                'CashOnDeliveryType' => 3,
            ],
        ]);

        ray(Arr::dot($payload));

        $this->assertEquals('CashOnDelivery', data_get($payload, 'Params.CarrierService'));
        $this->assertEquals(1, data_get($payload, 'Params.CashOnDelivery'));
        $this->assertEquals('PLN', data_get($payload, 'Params.CashOnDeliveryCurrency'));
        $this->assertEquals(3, data_get($payload, 'Params.CashOnDeliveryType'));
    }
}
