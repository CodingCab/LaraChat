<?php

namespace Tests\Feature;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataCollectionRecordTotalQuantityAdjustedTest extends TestCase
{
    use RefreshDatabase;
    public function test_total_quantity_adjusted_is_calculated_correctly()
    {
        // Create test data
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], [
            'warehouse_code' => $warehouse->code,
            'product_sku' => $product->sku,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        // Test case 1: Both transfers have values
        $record1 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 10.5,
            'total_transferred_out' => 5.3,
        ]);

        // Force reload to get computed column value
        $record1->refresh();
        $this->assertEquals(15.8, $record1->total_quantity_adjusted);

        // Test case 2: Only transferred_in has value
        $record2 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 25.0,
            'total_transferred_out' => 0,
        ]);

        $record2->refresh();
        $this->assertEquals(25.0, $record2->total_quantity_adjusted);

        // Test case 3: Only transferred_out has value
        $record3 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 0,
            'total_transferred_out' => 18.75,
        ]);

        $record3->refresh();
        $this->assertEquals(18.75, $record3->total_quantity_adjusted);

        // Test case 4: Both values are zero
        $record4 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 0,
            'total_transferred_out' => 0,
        ]);

        $record4->refresh();
        $this->assertEquals(0, $record4->total_quantity_adjusted);
    }

    public function test_total_quantity_adjusted_is_not_fillable()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], [
            'warehouse_code' => $warehouse->code,
            'product_sku' => $product->sku,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        // Create a record without trying to set total_quantity_adjusted
        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 10,
            'total_transferred_out' => 5,
        ]);

        $record->refresh();
        // Should be 15 (10 + 5), calculated automatically
        $this->assertEquals(15, $record->total_quantity_adjusted);

        // Verify that trying to update it directly throws an exception
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('The value specified for generated column');
        
        $record->total_quantity_adjusted = 999;
        $record->save();
    }

    public function test_total_quantity_adjusted_updates_when_transfers_change()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], [
            'warehouse_code' => $warehouse->code,
            'product_sku' => $product->sku,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'total_transferred_in' => 10,
            'total_transferred_out' => 5,
        ]);

        $record->refresh();
        $this->assertEquals(15, $record->total_quantity_adjusted);

        // Update the transfers
        $record->update([
            'total_transferred_in' => 20,
            'total_transferred_out' => 15,
        ]);

        $record->refresh();
        $this->assertEquals(35, $record->total_quantity_adjusted);
    }
}