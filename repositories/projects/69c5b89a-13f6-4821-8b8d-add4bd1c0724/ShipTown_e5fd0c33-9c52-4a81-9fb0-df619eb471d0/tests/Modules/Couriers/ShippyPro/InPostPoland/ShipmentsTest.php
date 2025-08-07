<?php

namespace Tests\Modules\Couriers\ShippyPro\InPostPoland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Warehouse;
use App\Modules\Couriers\ShippyPro\InPostPoland\src\ShippyProInPostPolandServiceProvider;
use App\User;
use Tests\TestCase;

class ShipmentsTest extends TestCase
{
    private string $uri = '/api/shipping-labels';
    protected OrderAddress $shippingAddress;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        if (!env('SHIPPY_PRO_API_KEY')) {
            $this->markTestSkipped('Environment variable SHIPPY_PRO_API_KEY does not exist.');
        }

        if (!env('SHIPPY_PRO_INPOST_POLAND_CARRIER_ID') || !env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME')) {
            $this->markTestSkipped('InPost Poland Carrier ID or Carrier Name is not set in the environment variables.');
        }

        ShippyProInPostPolandServiceProvider::enableModule();

        $this->shippingAddress = OrderAddress::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'address1' => 'ul. Testowa 1',
            'address2' => '',
            'city' => 'Warszawa',
            'postcode' => '00-001',
            'country_code' => 'POL',
            'country_name' => 'Polska',
            'phone' => '+48123456789',
            'locker_box_code' => 'ORZ09M',
        ]);

        $warehouseAddress = OrderAddress::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'address1' => 'ul. Plater Emilii 1',
            'address2' => '',
            'city' => 'Kraków',
            'postcode' => '30-009',
            'country_code' => 'POL',
            'country_name' => 'Polska',
            'phone' => '+48321654987'
        ]);

        $warehouse = Warehouse::factory()->create([
            'name' => 'Kraków',
            'code' => 'KRA',
            'address_id' => $warehouseAddress->getKey()
        ]);

        $this->user = User::factory()->create([
            'warehouse_code' => $warehouse->code,
            'warehouse_id' => $warehouse->getKey(),
        ]);
        $this->user->assignRole('admin');
    }

    public function testLockerStandardSizeXsShipment()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'inpost_paczkomaty_gabaryt_xs',
            'shipping_method_name' => 'inpost_paczkomaty_gabaryt_xs',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'inpost_paczkomaty_gabaryt_xs',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testLockerStandardSizeAShipment()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'inpost_paczkomaty_gabaryt_a',
            'shipping_method_name' => 'inpost_paczkomaty_gabaryt_a',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'inpost_paczkomaty_gabaryt_a',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testLockerStandardSizeBShipment()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'inpost_paczkomaty_gabaryt_b',
            'shipping_method_name' => 'inpost_paczkomaty_gabaryt_b',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'inpost_paczkomaty_gabaryt_b',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testLockerStandardSizeCShipment()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'inpost_paczkomaty_gabaryt_c',
            'shipping_method_name' => 'inpost_paczkomaty_gabaryt_c',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'inpost_paczkomaty_gabaryt_c',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }
}
