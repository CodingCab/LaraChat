<?php

namespace Tests\Modules\Couriers\ShippyPro\DpdPoland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Warehouse;
use App\Models\OrderPayment;
use App\Modules\Couriers\ShippyPro\DpdPoland\src\ShippyProDpdPolandServiceProvider;
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

        if (!env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID') || !env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME')) {
            $this->markTestSkipped('DPD Poland Carrier ID or Carrier Name is not set in the environment variables.');
        }

        ShippyProDpdPolandServiceProvider::enableModule();

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

    public function testStandardService()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'dpd_polska_standard',
            'shipping_method_name' => 'dpd_polska_standard',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'dpd_polska_standard',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testExpressService()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'dpd_polska_express',
            'shipping_method_name' => 'dpd_polska_express',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'dpd_polska_express',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME'),
            'service' => 'Express',
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testDropOffService()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'dpd_polska_odbior_w_punkcie',
            'shipping_method_name' => 'dpd_polska_odbior_w_punkcie',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'dpd_polska_odbior_w_punkcie',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testInternationalService()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'dpd_polska_international',
            'shipping_method_name' => 'dpd_polska_international',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'dpd_polska_international',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);
    }

    public function testCashOnDeliveryService()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_method_code' => 'dpd_polska_pobranie',
            'shipping_method_name' => 'dpd_polska_pobranie',
            'shipping_address_id' => $this->shippingAddress->getKey(),
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson($this->uri, [
            'shipping_service_code' => 'dpd_polska_pobranie',
            'order_id' => $order->getKey(),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_shipments', [
            'order_id' => $order->getKey(),
            'carrier' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME'),
            'service' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_SERVICE'),
            'tracking_url' => $response->json('data.0.tracking_url'),
        ]);

        $this->assertDatabaseHas('orders_payments', [
            'order_id' => $order->getKey(),
            'name' => t('Cash on Delivery'),
            'amount' => $order->total_outstanding,
        ]);

        $orderPayment = OrderPayment::where('order_id', $order->getKey())->first();
        $this->assertEquals(
            $response->json('data.0.shipping_number'),
            $orderPayment->additional_fields['shipping_number']
        );
    }
}
