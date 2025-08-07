<?php

namespace Tests\Modules\ActiveOrdersInventoryReservations\src\Jobs;

use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncMissingReservationsJobTest extends TestCase
{
    use RefreshDatabase;

    private Configuration $config;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();
        
        // Ensure we update the existing configuration rather than trying to create a new one
        $this->config = Configuration::query()->first();
        $this->config->update([
            'warehouse_id' => $this->warehouse->id,
        ]);
    }

    public function test_creates_missing_reservations_for_active_orders()
    {
        // Create products and inventory
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $inventory1 = Inventory::updateOrCreate([
            'product_id' => $product1->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        $inventory2 = Inventory::updateOrCreate([
            'product_id' => $product2->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 50,
        ]);

        // Create an active order
        $order = Order::factory()->create([
            'is_active' => true,
        ]);

        // Simulate order products created via SQL (bypass Eloquent events)
        DB::table('orders_products')->insert([
            [
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'name_ordered' => $product1->name,
                'sku_ordered' => $product1->sku,
                'quantity_ordered' => 5,
                'quantity_split' => 0,
                'quantity_shipped' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'product_id' => $product2->id,
                'name_ordered' => $product2->name,
                'sku_ordered' => $product2->sku,
                'quantity_ordered' => 3,
                'quantity_split' => 0,
                'quantity_shipped' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Verify no reservations exist yet
        $this->assertEquals(0, InventoryReservation::count());

        // Run the sync job
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Debug: Check if order products exist with correct data
        $dbOrderProducts = DB::table('orders_products')
            ->where('order_id', $order->id)
            ->get();
        
        $this->assertCount(2, $dbOrderProducts, 'Order products not found in database');
        
        foreach ($dbOrderProducts as $dbOrderProduct) {
            $this->assertGreaterThan(0, $dbOrderProduct->quantity_to_ship, 'quantity_to_ship is not greater than 0');
        }

        // Verify reservations were created
        $this->assertEquals(2, InventoryReservation::count());

        $orderProduct1 = OrderProduct::where('order_id', $order->id)
            ->where('product_id', $product1->id)
            ->first();
        
        $orderProduct2 = OrderProduct::where('order_id', $order->id)
            ->where('product_id', $product2->id)
            ->first();

        $reservation1 = InventoryReservation::where('inventory_id', $inventory1->id)->first();
        $this->assertEquals(5, $reservation1->quantity_reserved);
        $this->assertEquals($product1->sku, $reservation1->product_sku);
        $this->assertEquals($this->warehouse->code, $reservation1->warehouse_code);
        $this->assertEquals('Order #' . $order->order_number, $reservation1->comment);
        $this->assertEquals(ReservationsService::getUuid($orderProduct1), $reservation1->custom_uuid);

        $reservation2 = InventoryReservation::where('inventory_id', $inventory2->id)->first();
        $this->assertEquals(3, $reservation2->quantity_reserved);
        $this->assertEquals($product2->sku, $reservation2->product_sku);
        $this->assertEquals($this->warehouse->code, $reservation2->warehouse_code);
        $this->assertEquals('Order #' . $order->order_number, $reservation2->comment);
        $this->assertEquals(ReservationsService::getUuid($orderProduct2), $reservation2->custom_uuid);
    }

    public function test_ignores_inactive_orders()
    {
        $product = Product::factory()->create();
        
        Inventory::updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        // Create an inactive order
        $order = Order::factory()->create([
            'is_active' => false,
        ]);

        // Simulate order product created via SQL
        DB::table('orders_products')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
            'quantity_ordered' => 5,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the sync job
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Verify no reservations were created
        $this->assertEquals(0, InventoryReservation::count());
    }

    public function test_ignores_order_products_with_zero_quantity_to_ship()
    {
        $product = Product::factory()->create();
        
        Inventory::updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        $order = Order::factory()->create([
            'is_active' => true,
        ]);

        // Simulate order product with zero quantity to ship
        DB::table('orders_products')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
            'quantity_ordered' => 5,
            'quantity_split' => 0,
            'quantity_shipped' => 5, // All shipped, so quantity_to_ship will be 0
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the sync job
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Verify no reservations were created
        $this->assertEquals(0, InventoryReservation::count());
    }

    public function test_does_not_duplicate_existing_reservations()
    {
        $product = Product::factory()->create();
        
        $inventory = Inventory::updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        $order = Order::factory()->create([
            'is_active' => true,
        ]);

        // Create order product normally (this will trigger reservation creation)
        $orderProduct = OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
            'quantity_ordered' => 5,
            'quantity_to_ship' => 5,
        ]);

        // Wait for events to process
        sleep(1);

        // Verify reservation exists
        $initialCount = InventoryReservation::count();
        $this->assertGreaterThan(0, $initialCount);

        // Run the sync job
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Verify no duplicate reservations were created
        $this->assertEquals($initialCount, InventoryReservation::count());
    }

    public function test_handles_missing_inventory_gracefully()
    {
        $product = Product::factory()->create();
        
        // Delete any auto-created inventory records for this product
        Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->delete();
        
        // Verify no inventory exists for this product in the configured warehouse
        $inventoryCount = Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->count();
        $this->assertEquals(0, $inventoryCount, 'Inventory should not exist for this product');

        $order = Order::factory()->create([
            'is_active' => true,
        ]);

        // Simulate order product created via SQL
        DB::table('orders_products')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
            'quantity_ordered' => 5,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the sync job - should not throw exception
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Verify no reservations were created
        $this->assertEquals(0, InventoryReservation::count(), 'No reservations should be created when inventory is missing');
    }

    public function test_handles_null_warehouse_configuration()
    {
        // Clear warehouse configuration
        $this->config->update(['warehouse_id' => null]);

        $product = Product::factory()->create();
        
        Inventory::updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        $order = Order::factory()->create([
            'is_active' => true,
        ]);

        // Simulate order product created via SQL
        DB::table('orders_products')->insert([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
            'quantity_ordered' => 5,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the sync job
        $job = new SyncMissingReservationsJob();
        $job->handle();

        // Verify no reservations were created
        $this->assertEquals(0, InventoryReservation::count());
    }

    public function test_filters_orders_by_created_date()
    {
        $product = Product::factory()->create();
        
        Inventory::updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 100,
        ]);

        // Create an old order (should be excluded)
        $oldOrder = Order::factory()->create([
            'is_active' => true,
            'created_at' => now()->subDays(3),
        ]);

        // Create a recent order (should be included)
        $recentOrder = Order::factory()->create([
            'is_active' => true,
            'created_at' => now()->subMinutes(1),
        ]);

        // Simulate order products created via SQL
        DB::table('orders_products')->insert([
            [
                'order_id' => $oldOrder->id,
                'product_id' => $product->id,
                'name_ordered' => $product->name,
                'sku_ordered' => $product->sku,
                'quantity_ordered' => 5,
                'quantity_split' => 0,
                'quantity_shipped' => 0,
                'created_at' => $oldOrder->created_at,
                'updated_at' => $oldOrder->created_at,
            ],
            [
                'order_id' => $recentOrder->id,
                'product_id' => $product->id,
                'name_ordered' => $product->name,
                'sku_ordered' => $product->sku,
                'quantity_ordered' => 3,
                'quantity_split' => 0,
                'quantity_shipped' => 0,
                'created_at' => $recentOrder->created_at,
                'updated_at' => $recentOrder->created_at,
            ],
        ]);

        // Run the sync job with date filter (only orders from last 2 minutes)
        $job = new SyncMissingReservationsJob(now()->subMinutes(2));
        $job->handle();

        // Verify only the recent order got a reservation
        $this->assertEquals(1, InventoryReservation::count());
        
        $reservation = InventoryReservation::first();
        $this->assertEquals('Order #' . $recentOrder->order_number, $reservation->comment);
        $this->assertEquals(3, $reservation->quantity_reserved);
    }
}