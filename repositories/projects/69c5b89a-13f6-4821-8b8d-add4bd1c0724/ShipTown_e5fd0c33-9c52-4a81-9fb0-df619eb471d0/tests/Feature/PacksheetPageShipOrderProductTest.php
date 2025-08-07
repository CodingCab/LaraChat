<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PacksheetPageShipOrderProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Order $order;
    protected Product $product;
    protected OrderProduct $orderProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['warehouse_id' => 1]);
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create(['sku' => 'TEST-SKU-001']);
        $this->order = Order::factory()->create([
            'status_code' => 'picking',
            'total_products' => 5,
        ]);
        $this->orderProduct = OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'sku_ordered' => $this->product->sku,
            'quantity_ordered' => 5,
            'quantity_shipped' => 0,
        ]);
    }

    #[Test]
    public function it_ships_product_without_reloading_entire_ui()
    {
        $this->markTestSkipped('API endpoint not yet implemented');
        $this->actingAs($this->user);

        // Ship 2 units
        $response = $this->postJson('/api/orders/products/shipments', [
            'sku_shipped' => $this->orderProduct->sku_ordered,
            'product_id' => $this->orderProduct->product_id,
            'order_id' => $this->orderProduct->order_id,
            'order_product_id' => $this->orderProduct->id,
            'quantity_shipped' => 2,
        ]);

        $response->assertStatus(200);

        // Verify database updates
        $this->orderProduct->refresh();
        $this->assertEquals(2, $this->orderProduct->quantity_shipped);
        $this->assertEquals(3, $this->orderProduct->quantity_to_ship);
    }

    #[Test]
    public function it_correctly_updates_local_state_when_shipping_partial_quantity()
    {
        $this->markTestSkipped('API endpoint not yet implemented');
        $this->actingAs($this->user);

        // Get order products
        $response = $this->getJson('/api/order-products', [
            'filter[order_id]' => $this->order->id,
            'filter[warehouse_id]' => $this->warehouse->id,
            'include' => 'product,product.aliases,product.modelTags',
        ]);

        $response->assertStatus(200);
        $originalOrderProducts = $response->json('data');
        
        // Ship partial quantity
        $response = $this->postJson('/api/orders/products/shipments', [
            'sku_shipped' => $this->orderProduct->sku_ordered,
            'product_id' => $this->orderProduct->product_id,
            'order_id' => $this->orderProduct->order_id,
            'order_product_id' => $this->orderProduct->id,
            'quantity_shipped' => 2,
        ]);

        $response->assertStatus(200);

        // Verify order totals are updated
        $this->order->refresh();
        $this->assertEquals(2, $this->order->total_quantity_shipped);
    }

    #[Test]
    public function it_handles_shipping_all_remaining_quantity()
    {
        $this->markTestSkipped('API endpoint not yet implemented');
        $this->actingAs($this->user);

        // Ship all remaining quantity
        $response = $this->postJson('/api/orders/products/shipments', [
            'sku_shipped' => $this->orderProduct->sku_ordered,
            'product_id' => $this->orderProduct->product_id,
            'order_id' => $this->orderProduct->order_id,
            'order_product_id' => $this->orderProduct->id,
            'quantity_shipped' => 5,
        ]);

        $response->assertStatus(200);

        // Verify product is fully shipped
        $this->orderProduct->refresh();
        $this->assertEquals(5, $this->orderProduct->quantity_shipped);
        $this->assertEquals(0, $this->orderProduct->quantity_to_ship);
    }

    #[Test]
    public function it_reverts_local_state_on_api_error()
    {
        $this->markTestSkipped('API endpoint not yet implemented');
        $this->actingAs($this->user);

        // Try to ship invalid quantity (more than available)
        $response = $this->postJson('/api/orders/products/shipments', [
            'sku_shipped' => $this->orderProduct->sku_ordered,
            'product_id' => $this->orderProduct->product_id,
            'order_id' => $this->orderProduct->order_id,
            'order_product_id' => $this->orderProduct->id,
            'quantity_shipped' => 10, // More than quantity_to_ship
        ]);

        // This should fail validation
        $response->assertStatus(422);

        // Verify nothing was shipped
        $this->orderProduct->refresh();
        $this->assertEquals(0, $this->orderProduct->quantity_shipped);
        $this->assertEquals(5, $this->orderProduct->quantity_to_ship);
    }

    #[Test]
    public function it_handles_negative_quantity_for_returns()
    {
        $this->markTestSkipped('API endpoint not yet implemented');
        $this->actingAs($this->user);

        // First ship some quantity
        $this->orderProduct->update([
            'quantity_shipped' => 3,
        ]);

        // Return 1 unit (negative quantity)
        $response = $this->postJson('/api/orders/products/shipments', [
            'sku_shipped' => $this->orderProduct->sku_ordered,
            'product_id' => $this->orderProduct->product_id,
            'order_id' => $this->orderProduct->order_id,
            'order_product_id' => $this->orderProduct->id,
            'quantity_shipped' => -1,
        ]);

        $response->assertStatus(200);

        // Verify quantity was reduced
        $this->orderProduct->refresh();
        $this->assertEquals(2, $this->orderProduct->quantity_shipped);
        $this->assertEquals(3, $this->orderProduct->quantity_to_ship);
    }
}