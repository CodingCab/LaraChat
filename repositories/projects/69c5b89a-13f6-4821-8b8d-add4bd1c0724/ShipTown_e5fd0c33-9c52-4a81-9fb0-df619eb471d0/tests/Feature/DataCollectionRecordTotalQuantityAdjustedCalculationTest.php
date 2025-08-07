<?php

namespace Tests\Feature;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class DataCollectionRecordTotalQuantityAdjustedCalculationTest extends TestCase
{
    public function test_total_quantity_adjusted_is_calculated_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        // Test case 1: Only transferred in
        $record1 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'total_transferred_in' => 10,
            'total_transferred_out' => 0,
        ]);

        $record1->refresh();
        $this->assertEquals(10, $record1->total_quantity_adjusted, 'When only transferred in, adjusted should equal transferred in');

        // Test case 2: Only transferred out
        $record2 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'total_transferred_in' => 0,
            'total_transferred_out' => 5,
        ]);

        $record2->refresh();
        $this->assertEquals(-5, $record2->total_quantity_adjusted, 'When only transferred out, adjusted should be negative');

        // Test case 3: Both transferred in and out
        $record3 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'total_transferred_in' => 20,
            'total_transferred_out' => 8,
        ]);

        $record3->refresh();
        $this->assertEquals(12, $record3->total_quantity_adjusted, 'Adjusted should be in minus out');

        // Test case 4: Transferred out exceeds transferred in
        $record4 = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'total_transferred_in' => 5,
            'total_transferred_out' => 15,
        ]);

        $record4->refresh();
        $this->assertEquals(-10, $record4->total_quantity_adjusted, 'When out exceeds in, adjusted should be negative');
    }

    public function test_total_quantity_adjusted_handles_null_values()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        // Create record without explicitly setting transfer values
        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
        ]);

        $record->refresh();
        $this->assertEquals(0, $record->total_quantity_adjusted, 'Null values should be treated as 0');
    }
}