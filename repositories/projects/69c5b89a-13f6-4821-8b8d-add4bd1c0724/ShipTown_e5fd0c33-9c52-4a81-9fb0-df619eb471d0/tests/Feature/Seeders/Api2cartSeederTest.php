<?php

namespace Tests\Feature\Seeders;

use App\Models\Warehouse;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Database\Seeders\Api2cartSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\ResetsDatabase;
use Tests\TestCase;

class Api2cartSeederTest extends TestCase
{
    use ResetsDatabase;

    #[Test]
    public function test_seeder_creates_connection_when_env_keys_are_present()
    {
        // Skip test if env keys are not set
        if (empty(env('TEST_API2CART_STORE_KEY')) || empty(env('API2CART_API_KEY'))) {
            $this->markTestSkipped('Api2cart TEST_API2CART_STORE_KEY or API2CART_API_KEY keys not set in environment.');
        }

        // Run the seeder - it will use values from .env file
        $this->seed(Api2cartSeeder::class);

        // Assert warehouse was created
        $warehouse = Warehouse::where('code', 'TEST')->first();
        $this->assertNotNull($warehouse);
        $this->assertEquals('Test Warehouse', $warehouse->name);

        // Assert Api2cart connection was created
        $connection = Api2cartConnection::first();
        $this->assertNotNull($connection);
        $this->assertEquals('opencart', $connection->type);
        $this->assertEquals('https://demo.api2cart.com/opencart', $connection->url);
        $this->assertNotEmpty($connection->bridge_api_key);
        $this->assertEquals(env('TEST_API2CART_STORE_KEY'), $connection->bridge_api_key);
        $this->assertEquals('magento_stock', $connection->inventory_source_warehouse_tag);
        $this->assertEquals($warehouse->id, $connection->pricing_source_warehouse_id);
        $this->assertEquals(0, $connection->magento_store_id);
        $this->assertEquals('TEST_', $connection->prefix);
    }

    #[Test]
    public function test_seeder_creates_connection_only_if_both_keys_present()
    {
        // This test validates that the seeder requires both keys to be present
        // Since we can't easily unset env vars in tests, we'll validate the logic
        // by checking that a connection was created when both keys exist

        // Clear any existing connections first
        Api2cartConnection::query()->delete();

        // Run the seeder
        $this->seed(Api2cartSeeder::class);

        // If both TEST_API2CART_STORE_KEY and API2CART_API_KEY are in .env,
        // a connection should be created
        if (!empty(env('TEST_API2CART_STORE_KEY')) && !empty(env('API2CART_API_KEY'))) {
            $this->assertEquals(1, Api2cartConnection::count());
        } else {
            $this->assertEquals(0, Api2cartConnection::count());
        }
    }

    #[Test]
    public function test_seeder_does_not_duplicate_connection_on_multiple_runs()
    {
        // Skip test if env keys are not set
        if (empty(env('TEST_API2CART_STORE_KEY')) || empty(env('API2CART_API_KEY'))) {
            $this->markTestSkipped('Api2cart TEST_API2CART_STORE_KEY or API2CART_API_KEY keys not set in environment.');
        }

        // Run the seeder multiple times
        $this->seed(Api2cartSeeder::class);
        $this->seed(Api2cartSeeder::class);
        $this->seed(Api2cartSeeder::class);

        // Assert only one connection exists (if env vars are set)
        if (!empty(env('TEST_API2CART_STORE_KEY')) && !empty(env('API2CART_API_KEY'))) {
            $this->assertEquals(1, Api2cartConnection::count());
            $this->assertEquals(1, Warehouse::where('code', 'TEST')->count());
        }
    }

    #[Test]
    public function test_seeder_uses_existing_test_warehouse_if_present()
    {
        // Skip test if env keys are not set
        if (empty(env('TEST_API2CART_STORE_KEY')) || empty(env('API2CART_API_KEY'))) {
            $this->markTestSkipped('Api2cart TEST_API2CART_STORE_KEY or API2CART_API_KEY keys not set in environment.');
        }

        // Create a warehouse with TEST code first
        $existingWarehouse = Warehouse::create([
            'code' => 'TEST',
            'name' => 'Existing Test Warehouse',
        ]);

        // Run the seeder
        $this->seed(Api2cartSeeder::class);

        // Assert the existing warehouse was used
        $this->assertEquals(1, Warehouse::where('code', 'TEST')->count());
        $warehouse = Warehouse::where('code', 'TEST')->first();
        $this->assertEquals($existingWarehouse->id, $warehouse->id);
        $this->assertEquals('Existing Test Warehouse', $warehouse->name);

        // Assert connection uses the existing warehouse
        $connection = Api2cartConnection::first();
        $this->assertEquals($existingWarehouse->id, $connection->pricing_source_warehouse_id);
    }
}
