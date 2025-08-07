<?php

namespace Tests\Feature\Api\CsvImport;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatesExistingRecordTest extends TestCase
{
    #[Test]
    public function test_import_updates_existing_record_instead_of_creating_new(): void
    {
        $user = User::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create(['sku' => '4004']);

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $collection */
        $collection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $collection->id,
            'inventory_id' => $inventory->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'product_id' => $product->id,
            'quantity_requested' => 5,
            'quantity_scanned' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->postJson(route('api.csv-import.store'), [
            'data_collection_id' => $collection->getKey(),
            'data' => [
                [
                    'product_sku' => '4004',
                    'quantity_scanned' => 6,
                ],
            ],
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseCount('data_collection_records', 1);

        $this->assertDatabaseHas('data_collection_records', [
            'data_collection_id' => $collection->id,
            'product_id' => $product->id,
            'quantity_requested' => 5,
            'quantity_scanned' => 6,
        ]);
    }
}
