<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Models\OrderStatus;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Order;
use App\Modules\Api2cart\src\Jobs\SyncOrderStatus;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncOrderStatusTest extends TestCase
{

    #[Test]
    public function it_generates_unique_id_per_order()
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        $job1 = new SyncOrderStatus($order1);
        $job2 = new SyncOrderStatus($order2);

        $this->assertNotEquals($job1->uniqueId(), $job2->uniqueId());
    }

    #[Test]
    public function it_handles_order_status_sync_successfully()
    {
        $api2CartConnection = Api2cartConnection::factory()->create();
        Api2cartOrderImports::truncate();
        $api2CartOrderImport = Api2cartOrderImports::factory()->create();
        $api2CartOrderImport->update(['connection_id' => $api2CartConnection->id]);

        OrderStatus::query()->updateOrCreate([
            'code' => 'completed',
        ], [
            'name' => 'completed',
            'sync_ecommerce' => true,
        ]);

        $order = Order::factory()->create(['order_number' => $api2CartOrderImport->order_number, 'status_code' => 'completed']);

        // Set API key in config
        config(['app.api2cart_api_key' => 'test-api-key']);

        // Mock HTTP response for order.update.json
        Http::fake([
            'api.api2cart.com/v1.1/order.update.json*' => Http::response([
                'return_code' => 0,
                'return_message' => 'Success',
            ], 200),
        ]);


        $job = new SyncOrderStatus($order);
        $result = $job->handle();

        ray(Api2cartOrderImports::query()->get()->toArray());

        $this->assertTrue($result);
        $api2CartOrderImport->refresh();
        $this->assertTrue($api2CartOrderImport->order_status_in_sync);
    }

    #[Test]
    public function it_handles_order_status_sync_failure()
    {
        $api2CartConnection = Api2cartConnection::factory()->create();
        Api2cartOrderImports::truncate();
        $api2CartOrderImport = Api2cartOrderImports::factory()->create();
        $api2CartOrderImport->update(['connection_id' => $api2CartConnection->id]);

        OrderStatus::query()->updateOrCreate([
            'code' => 'completed',
        ], [
            'name' => 'completed',
            'sync_ecommerce' => true,
        ]);

        $order = Order::factory()->create(['order_number' => $api2CartOrderImport->order_number, 'status_code' => 'completed']);

        // Set API key in config
        config(['app.api2cart_api_key' => 'test-api-key']);

        // Mock HTTP response for order.update.json with failure
        Http::fake([
            'api.api2cart.com/v1.1/order.update.json*' => Http::response([
                'return_code' => 2,
                'return_message' => 'Error updating order',
            ], 200),
        ]);


        $job = new SyncOrderStatus($order);
        $result = $job->handle();

        ray(Api2cartOrderImports::query()->get()->toArray());

        $this->assertFalse($result);
        $api2CartOrderImport->refresh();
        $this->assertFalse($api2CartOrderImport->order_status_in_sync);
    }
}
