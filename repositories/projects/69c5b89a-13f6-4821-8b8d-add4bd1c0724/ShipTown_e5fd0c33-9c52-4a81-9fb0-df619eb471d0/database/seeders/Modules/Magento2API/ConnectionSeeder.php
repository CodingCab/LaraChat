<?php

namespace Database\Seeders\Modules\Magento2API;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductRecordsExistJob;
use App\Modules\Magento2API\PriceSync\src\Models\MagentoConnection;
use Illuminate\Database\Seeder;

class ConnectionSeeder extends Seeder
{
    public function run(): void
    {
        if (empty(env('TEST_MODULES_MAGENTO2MSI_BASE_URL'))) {
            return;
        }

        $warehouse = Warehouse::query()->inRandomOrder()->first() ?? Warehouse::factory()->create();

        $product = Product::query()->inRandomOrder()->first() ?? Product::factory()->create(['sku' => '45']);
        $product->attachTag('Available Online');

        $connection = MagentoConnection::query()->updateOrCreate([
            'base_url' => env('TEST_MODULES_MAGENTO2MSI_BASE_URL'),
        ], [
            'api_access_token' => env('TEST_MODULES_MAGENTO2MSI_ACCESS_TOKEN'),
            'pricing_source_warehouse_id' => $warehouse->id,
        ]);

        $connection->save();

        EnsureProductRecordsExistJob::dispatch();
    }
}
