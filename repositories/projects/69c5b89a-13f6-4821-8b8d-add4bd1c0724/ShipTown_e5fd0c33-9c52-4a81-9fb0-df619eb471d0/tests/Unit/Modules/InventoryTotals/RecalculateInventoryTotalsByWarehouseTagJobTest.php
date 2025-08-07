<?php

namespace Tests\Unit\Modules\InventoryTotals;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryTotalsByWarehouseTagJob;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Tags\Tag;
use Tests\TestCase;

class RecalculateInventoryTotalsByWarehouseTagJobTest extends TestCase
{
    #[Test]
    public function test_handles_null_values_when_no_inventory_exists(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        // Create a product and warehouse
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        // Create a tag and attach it to the warehouse
        $warehouse->attachTag('test-tag');
        
        // Ensure the inventory total record exists and needs recalculation
        InventoryTotalByWarehouseTag::query()
            ->where('product_id', $product->id)
            ->where('tag_id', Tag::findFromString('test-tag')->id)
            ->update(['recalc_required' => true]);
        
        // Run the job
        RecalculateInventoryTotalsByWarehouseTagJob::dispatch();
        
        // Get the updated record
        $inventoryTotal = InventoryTotalByWarehouseTag::query()
            ->where('product_id', $product->id)
            ->where('tag_id', Tag::findFromString('test-tag')->id)
            ->first();
        
        // Assert that the quantity fields are set to 0, not NULL
        $this->assertEquals(0, $inventoryTotal->quantity);
        $this->assertEquals(0, $inventoryTotal->quantity_reserved);
        $this->assertEquals(0, $inventoryTotal->quantity_incoming);
        // Check that max_inventory_updated_at is not null (it should be set to the default value)
        $this->assertNotNull($inventoryTotal->max_inventory_updated_at);
        $this->assertFalse($inventoryTotal->recalc_required);
    }
    
    #[Test]
    public function test_correctly_calculates_quantities_when_inventory_exists(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        // Create a product and warehouse
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        // Create a tag and attach it to the warehouse
        $warehouse->attachTag('warehouse-group');
        
        // Get the inventory record and update it
        $inventory = Inventory::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();
            
        $inventory->update([
            'quantity' => 100,
            'quantity_reserved' => 20,
            'quantity_incoming' => 50,
        ]);
        
        // Ensure the inventory total record exists and needs recalculation
        InventoryTotalByWarehouseTag::query()
            ->where('product_id', $product->id)
            ->where('tag_id', Tag::findFromString('warehouse-group')->id)
            ->update(['recalc_required' => true]);
        
        // Run the job
        RecalculateInventoryTotalsByWarehouseTagJob::dispatch();
        
        // Get the updated record
        $inventoryTotal = InventoryTotalByWarehouseTag::query()
            ->where('product_id', $product->id)
            ->where('tag_id', Tag::findFromString('warehouse-group')->id)
            ->first();
        
        // Assert that the quantities are correctly calculated
        $this->assertEquals(100, $inventoryTotal->quantity);
        $this->assertEquals(20, $inventoryTotal->quantity_reserved);
        $this->assertEquals(50, $inventoryTotal->quantity_incoming);
        $this->assertNotNull($inventoryTotal->max_inventory_updated_at);
        $this->assertNotEquals('2000-01-01 00:00:00', $inventoryTotal->max_inventory_updated_at);
        $this->assertFalse($inventoryTotal->recalc_required);
    }
}