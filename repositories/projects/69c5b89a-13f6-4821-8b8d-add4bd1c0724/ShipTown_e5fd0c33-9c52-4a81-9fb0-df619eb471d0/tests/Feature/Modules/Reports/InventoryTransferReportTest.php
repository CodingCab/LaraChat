<?php

namespace Tests\Feature\Modules\Reports;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Reports\src\Models\InventoryTransferReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTransferReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_transfer_report_includes_total_adjusted_columns()
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

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => 20.00,
            'unit_sold_price' => 40.00,
            'total_transferred_in' => 25,
            'total_transferred_out' => 10,
            'quantity_scanned' => 15,
        ]);

        $report = new InventoryTransferReport();
        
        // Check that the new fields are available in the report
        $fields = collect($report->fields());
        
        $this->assertTrue($fields->contains('field', 'Total Adjusted Quantity'));
        $this->assertTrue($fields->contains('field', 'Total Adjusted Cost'));
        $this->assertTrue($fields->contains('field', 'Total Adjusted Sold Price'));

        // Verify the query can be executed
        $query = $report->baseQuery;
        $results = $query->get();
        
        $this->assertCount(1, $results);
        
        $record = $results->first();
        
        // Verify calculations
        // total_adjusted_quantity = 25 - 10 = 15
        $this->assertEquals(15, $record->total_adjusted_quantity);
        
        // total_adjusted_cost = 15 * 20.00 = 300.00
        $this->assertEquals(300.00, $record->total_adjusted_cost);
        
        // total_adjusted_sold_price = 15 * 40.00 = 600.00
        $this->assertEquals(600.00, $record->total_adjusted_sold_price);
    }

    public function test_inventory_transfer_report_aggregates_multiple_records()
    {
        $warehouse = Warehouse::factory()->create();
        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
        ]);

        // Create multiple records for the same product
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => 10.00,
            'unit_sold_price' => 20.00,
            'total_transferred_in' => 10,
            'total_transferred_out' => 5,
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
            'unit_cost' => 10.00,
            'unit_sold_price' => 20.00,
            'total_transferred_in' => 15,
            'total_transferred_out' => 8,
        ]);

        $report = new InventoryTransferReport();
        $query = $report->baseQuery->selectRaw('
            SUM(data_collection_records.total_adjusted_quantity) as total_adjusted_quantity_sum,
            SUM(data_collection_records.total_adjusted_cost) as total_adjusted_cost_sum,
            SUM(data_collection_records.total_adjusted_sold_price) as total_adjusted_sold_price_sum
        ');
        
        $result = $query->first();
        
        // First record: total_adjusted_quantity = 10 - 5 = 5
        // Second record: total_adjusted_quantity = 15 - 8 = 7
        // Total: 5 + 7 = 12
        $this->assertEquals(12, $result->total_adjusted_quantity_sum);
        
        // First record: total_adjusted_cost = 5 * 10 = 50
        // Second record: total_adjusted_cost = 7 * 10 = 70
        // Total: 50 + 70 = 120
        $this->assertEquals(120, $result->total_adjusted_cost_sum);
        
        // First record: total_adjusted_sold_price = 5 * 20 = 100
        // Second record: total_adjusted_sold_price = 7 * 20 = 140
        // Total: 100 + 140 = 240
        $this->assertEquals(240, $result->total_adjusted_sold_price_sum);
    }
}