<?php

namespace Tests\External\ShippyPro\DpdPoland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\ShippingService;
use App\Modules\Couriers\ShippyPro\DpdPoland\src\Services\DpdPolandExpressService;
use App\Modules\Couriers\ShippyPro\DpdPoland\src\Services\DpdPolandStandardService;
use App\Modules\DpdUk\src\Models\Connection;
use App\Modules\DpdUk\src\Services\DpdUkService;
use Carbon\Carbon;
use Tests\TestCase;

class GenerateLabelDocumentJobTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test_create_shipping_label(): void
    {
        $order = Order::factory()->create();
        $shippingService = ShippingService::query()
            ->where('service_provider_class', DpdPolandExpressService::class)
            ->first();

        $shipment = (new DpdPolandExpressService())->ship($order->getKey(), $shippingService);

        $this->assertGreaterThan(0, $shipment->count());
    }
}
