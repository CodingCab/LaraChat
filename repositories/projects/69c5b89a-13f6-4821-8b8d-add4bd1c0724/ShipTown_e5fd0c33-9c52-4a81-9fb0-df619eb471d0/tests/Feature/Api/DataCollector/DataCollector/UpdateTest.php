<?php

namespace Tests\Feature\Api\DataCollector\DataCollector;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\PointOfSaleConfiguration\src\Models\PointOfSaleConfiguration;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    #[Test]
    public function test_transfer_in_scanned_action_call_returns_ok(): void
    {
        $randomQuantity = 10;

        $user = User::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()->firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $dataCollector */
        $dataCollector = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Test',
        ]);

        /** @var DataCollectionRecord $dataCollectorRecord */
        $dataCollectorRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollector->id,
            'inventory_id' => $inventory->id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => $randomQuantity,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('api.data-collector.update', [
            'data_collector' => $dataCollector->getKey(),
        ]), [
            'action' => 'transfer_in_scanned',
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertEquals($randomQuantity, $inventory->refresh()->quantity);

        $this->assertEquals(0, $dataCollectorRecord->refresh()->quantity_scanned);

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    #[Test]
    public function test_transfer_out_scanned_action_call_returns_ok(): void
    {
        $randomQuantity = 10;

        $user = User::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $dataCollector */
        $dataCollector = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'name' => 'Test',
        ]);

        /** @var DataCollectionRecord $dataCollectorRecord */
        $dataCollectorRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollector->id,
            'inventory_id' => $inventory->id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => $randomQuantity,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('api.data-collector.update', [
            'data_collector' => $dataCollector->getKey(),
        ]), [
            'action' => 'transfer_out_scanned',
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertEquals($randomQuantity * -1, $inventory->refresh()->quantity);

        $this->assertEquals(0, $dataCollectorRecord->refresh()->quantity_scanned);

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    #[Test]
    public function test_update_next_transaction_number_action_call_returns_ok(): void
    {
        $user = User::factory()->create();

        PointOfSaleConfiguration::query()->update([
            'next_transaction_number' => 1,
        ]);

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $dataCollector */
        $dataCollector = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'name' => 'Test',
        ]);

        /** @var DataCollectionRecord $dataCollectorRecord */
        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollector->id,
            'inventory_id' => $inventory->id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => 1,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('api.data-collector.update', [
            'data_collector' => $dataCollector->getKey(),
        ]), [
            'action' => 'update_next_transaction_number',
            'custom_uuid' => null,
            'deleted_at' => now()->toISOString()
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseHas('modules_point_of_sale_configuration', [
            'next_transaction_number' => 2,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}
