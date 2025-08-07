<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\ImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Database\Factories\Modules\Api2cart\src\Models\Api2cartOrderImportsFactory;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportOrdersJobsTest extends TestCase
{

    #[Test]
    public function it_does_not_create_duplicates()
    {
        Api2cartOrderImports::truncate();
        $connection = Api2cartConnection::factory()->create();

        $rawOrder = Api2cartOrderImportsFactory::new()->make()->raw_import;
        $rawOrder['id'] = '1001';
        $rawOrder['order_id'] = '500';
        $rawOrder['modified_at']['value'] = now()->format('Y-m-d\TH:i:sO');
        $rawOrder['modified_at']['format'] = 'Y-m-d\TH:i:sO';

        // Set API key in config
        config(['app.api2cart_api_key' => 'test-api-key']);

        // Mock HTTP responses for order.list.json
        Http::fake([
            'api.api2cart.com/v1.1/order.list.json*' => Http::sequence()
                ->push([
                    'return_code' => 0,
                    'return_message' => 'Success',
                    'result' => [
                        'orders_count' => 1,
                        'order' => [$rawOrder]
                    ]
                ], 200)
                ->push([
                    'return_code' => 0,
                    'return_message' => 'Success',
                    'result' => [
                        'orders_count' => 0,
                        'order' => []
                    ]
                ], 200)
                ->push([
                    'return_code' => 0,
                    'return_message' => 'Success',
                    'result' => [
                        'orders_count' => 1,
                        'order' => [$rawOrder]
                    ]
                ], 200)
                ->push([
                    'return_code' => 0,
                    'return_message' => 'Success',
                    'result' => [
                        'orders_count' => 0,
                        'order' => []
                    ]
                ], 200),
        ]);

        $job = new ImportOrdersJobs($connection);
        $job->handle();
        $job->handle();

        $this->assertEquals(1, Api2cartOrderImports::where('connection_id', $connection->id)->count());
    }
}
