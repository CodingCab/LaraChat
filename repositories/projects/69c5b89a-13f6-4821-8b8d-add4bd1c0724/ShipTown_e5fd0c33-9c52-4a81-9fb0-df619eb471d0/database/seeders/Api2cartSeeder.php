<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Modules\Api2cart\src\Api2cartServiceProvider;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Illuminate\Database\Seeder;

class Api2cartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if Api2cart test keys are present in environment
        if (empty(env('TEST_API2CART_STORE_KEY')) || empty(env('API2CART_API_KEY'))) {
            return;
        }

        // Get or create a warehouse for pricing source
        $warehouse = Warehouse::query()->firstOrCreate(
            ['code' => 'TEST'],
            [
                'name' => 'Test Warehouse',
            ]
        );

        // Create Api2cart connection
        Api2cartConnection::query()->firstOrCreate(
            [
                'type' => 'opencart',
                'url' => 'https://demo.api2cart.com/opencart',
            ],
            [
                'bridge_api_key' => env('TEST_API2CART_STORE_KEY'),
                'inventory_source_warehouse_tag' => 'magento_stock',
                'pricing_source_warehouse_id' => $warehouse->getKey(),
                'magento_store_id' => 0,
                'prefix' => 'TEST_',
            ]
        );

        Api2cartServiceProvider::enableModule();
    }
}
