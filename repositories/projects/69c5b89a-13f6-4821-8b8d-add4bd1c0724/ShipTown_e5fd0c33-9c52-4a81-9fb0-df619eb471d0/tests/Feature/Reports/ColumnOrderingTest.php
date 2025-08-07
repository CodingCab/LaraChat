<?php

namespace Tests\Feature\Reports;

use App\User;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;

class ColumnOrderingTest extends TestCase
{
    public function test_report_respects_column_order_parameter()
    {
        $user = User::factory()->create();
        
        // Create test data
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        
        // Test with specified column order using the correct column names
        $response = $this->actingAs($user, 'api')
            ->getJson('/api/reports/inventory?order=quantity_warehouse,sku,name,warehouse_code');
        
        $response->assertOk();
        
        // Check that columns are returned in the specified order
        $columns = $response->json('meta.columns');
        $columnNames = collect($columns)->pluck('name')->toArray();
        
        // Debug: print the first few column names to see the actual order
        // dd(array_slice($columnNames, 0, 4));
        
        // Check that the ordered columns appear at the beginning in the correct order
        $this->assertEquals('quantity_warehouse', $columnNames[0]);
        $this->assertEquals('sku', $columnNames[1]);
        $this->assertEquals('name', $columnNames[2]);
        $this->assertEquals('warehouse_code', $columnNames[3]);
    }
    
    public function test_report_works_without_order_parameter()
    {
        $user = User::factory()->create();
        
        // Create test data
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        
        // Test without order parameter
        $response = $this->actingAs($user, 'api')
            ->getJson('/api/reports/inventory');
        
        $response->assertOk();
        
        // Should return columns in default order
        $columns = $response->json('meta.columns');
        $this->assertNotEmpty($columns);
    }
}