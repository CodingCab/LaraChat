<?php

namespace Tests\Feature\DataCollectionRecords;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Tests\TestCase;

class TotalAdjustedCostAndSoldPriceTest extends TestCase
{
    public function test_total_adjusted_cost_is_calculated_correctly()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $dataCollectionRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => 10.50,
            'unit_sold_price' => 25.00,
            'total_transferred_in' => 15,
            'total_transferred_out' => 5,
        ]);

        $dataCollectionRecord->refresh();

        // total_adjusted_quantity = total_transferred_in - total_transferred_out = 15 - 5 = 10
        $expectedTotalAdjustedQuantity = 10;
        $this->assertEquals($expectedTotalAdjustedQuantity, $dataCollectionRecord->total_adjusted_quantity);

        // total_adjusted_cost = total_adjusted_quantity * unit_cost = 10 * 10.50 = 105.00
        $expectedTotalAdjustedCost = 105.00;
        $this->assertEquals($expectedTotalAdjustedCost, $dataCollectionRecord->total_adjusted_cost);

        // total_adjusted_sold_price = total_adjusted_quantity * unit_sold_price = 10 * 25.00 = 250.00
        $expectedTotalAdjustedSoldPrice = 250.00;
        $this->assertEquals($expectedTotalAdjustedSoldPrice, $dataCollectionRecord->total_adjusted_sold_price);
    }

    public function test_total_adjusted_calculations_handle_null_values()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $dataCollectionRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => null,
            'unit_sold_price' => null,
            'total_transferred_in' => 10,
            'total_transferred_out' => 3,
        ]);

        $dataCollectionRecord->refresh();

        // total_adjusted_quantity = 10 - 3 = 7
        $this->assertEquals(7, $dataCollectionRecord->total_adjusted_quantity);

        // With null unit costs, the calculated columns should be 0
        $this->assertEquals(0, $dataCollectionRecord->total_adjusted_cost);
        $this->assertEquals(0, $dataCollectionRecord->total_adjusted_sold_price);
    }

    public function test_total_adjusted_calculations_with_negative_adjustments()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        $dataCollectionRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => 15.75,
            'unit_sold_price' => 30.00,
            'total_transferred_in' => 5,
            'total_transferred_out' => 10,
        ]);

        $dataCollectionRecord->refresh();

        // total_adjusted_quantity = 5 - 10 = -5 (negative adjustment)
        $this->assertEquals(-5, $dataCollectionRecord->total_adjusted_quantity);

        // total_adjusted_cost = -5 * 15.75 = -78.75
        $expectedTotalAdjustedCost = -78.75;
        $this->assertEquals($expectedTotalAdjustedCost, $dataCollectionRecord->total_adjusted_cost);

        // total_adjusted_sold_price = -5 * 30.00 = -150.00
        $expectedTotalAdjustedSoldPrice = -150.00;
        $this->assertEquals($expectedTotalAdjustedSoldPrice, $dataCollectionRecord->total_adjusted_sold_price);
    }
}