<?php

namespace Tests\Feature\Api\Reports\InventoryTransfers;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestinationWarehouseColumnsTest extends TestCase
{
    #[Test]
    public function test_destination_warehouse_columns_are_included_in_report(): void
    {
        $user = User::factory()->create();

        $sourceWarehouse = Warehouse::factory()->create([
            'code' => 'SRC01',
            'name' => 'Source Warehouse',
        ]);

        $destinationWarehouse = Warehouse::factory()->create([
            'code' => 'DST01',
            'name' => 'Destination Warehouse',
        ]);

        $product = Product::factory()->create();

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $sourceWarehouse->id,
            'warehouse_code' => $sourceWarehouse->code,
            'destination_warehouse_code' => $destinationWarehouse->code,
            'type' => 'DataCollectionTransferOut',
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'warehouse_id' => $sourceWarehouse->id,
            'warehouse_code' => $sourceWarehouse->code,
            'quantity_requested' => 10,
            'total_transferred_out' => 10,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/reports/inventory-transfers');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'warehouse_code',
                    'destination_warehouse_code',
                    'destination_warehouse_name',
                ],
            ],
        ]);

        $firstRecord = $response->json('data.0');
        $this->assertEquals('SRC01', $firstRecord['warehouse_code']);
        $this->assertEquals('DST01', $firstRecord['destination_warehouse_code']);
        $this->assertEquals('Destination Warehouse', $firstRecord['destination_warehouse_name']);
    }

    #[Test]
    public function test_destination_warehouse_columns_are_null_when_no_destination(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create([
            'code' => 'WH01',
            'name' => 'Main Warehouse',
        ]);

        $product = Product::factory()->create();

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'destination_warehouse_code' => null,
            'type' => 'DataCollectionStocktake',
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'quantity_requested' => 5,
        ]);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/reports/inventory-transfers');

        $response->assertOk();

        $firstRecord = $response->json('data.0');
        $this->assertEquals('WH01', $firstRecord['warehouse_code']);
        $this->assertNull($firstRecord['destination_warehouse_code']);
        $this->assertNull($firstRecord['destination_warehouse_name']);
    }
}