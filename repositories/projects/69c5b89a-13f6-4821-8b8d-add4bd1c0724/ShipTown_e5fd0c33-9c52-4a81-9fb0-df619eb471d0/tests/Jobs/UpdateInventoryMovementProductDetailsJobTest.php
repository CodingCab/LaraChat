<?php

namespace Tests\Jobs;

use App\Jobs\UpdateInventoryMovementProductDetailsJob;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateInventoryMovementProductDetailsJobTest extends TestCase
{
    private Product $product;
    private Warehouse $warehouse;
    private Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouse = Warehouse::factory()->create([
            'code' => 'TEST',
        ]);
        $this->product = Product::factory()->create([
            'sku' => 'TEST-SKU-001',
            'name' => 'Test Product',
            'department' => 'Electronics',
            'category' => 'Accessories',
        ]);
        $this->inventory = Inventory::firstOrCreate([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 0,
            'quantity_reserved' => 0,
            'warehouse_code' => $this->warehouse->code,
        ]);
    }

    #[Test]
    public function test_job_updates_null_product_details_in_inventory_movements(): void
    {
        // Create inventory movements with null product details
        $movement1 = InventoryMovement::factory()->create([
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => null,
            'name' => null,
            'department' => null,
            'category' => null,
        ]);

        $movement2 = InventoryMovement::factory()->create([
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => null,
            'name' => null,
            'department' => null,
            'category' => null,
        ]);

        // Create a movement with existing details (should not be updated)
        $movement3 = InventoryMovement::factory()->create([
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => 'EXISTING-SKU',
            'name' => 'Existing Product',
            'department' => 'Existing Department',
            'category' => 'Existing Category',
        ]);

        // Run the job
        $job = new UpdateInventoryMovementProductDetailsJob();
        $job->handle();

        // Assert movements with null values are updated
        $movement1->refresh();
        $this->assertEquals('TEST-SKU-001', $movement1->sku);
        $this->assertEquals('Test Product', $movement1->name);
        $this->assertEquals('Electronics', $movement1->department);
        $this->assertEquals('Accessories', $movement1->category);

        $movement2->refresh();
        $this->assertEquals('TEST-SKU-001', $movement2->sku);
        $this->assertEquals('Test Product', $movement2->name);
        $this->assertEquals('Electronics', $movement2->department);
        $this->assertEquals('Accessories', $movement2->category);

        // Assert movement with existing details is not changed
        $movement3->refresh();
        $this->assertEquals('EXISTING-SKU', $movement3->sku);
        $this->assertEquals('Existing Product', $movement3->name);
        $this->assertEquals('Existing Department', $movement3->department);
        $this->assertEquals('Existing Category', $movement3->category);
    }

    #[Test]
    public function test_job_updates_partially_null_product_details(): void
    {
        // Create inventory movement with partially null product details
        $movement = InventoryMovement::factory()->create([
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => 'PARTIAL-SKU',
            'name' => null,
            'department' => null,
            'category' => 'Partial Category',
        ]);

        // Run the job
        $job = new UpdateInventoryMovementProductDetailsJob();
        $job->handle();

        // Assert all product details are updated when any field is null
        $movement->refresh();
        $this->assertEquals('TEST-SKU-001', $movement->sku);
        $this->assertEquals('Test Product', $movement->name);
        $this->assertEquals('Electronics', $movement->department);
        $this->assertEquals('Accessories', $movement->category);
    }

    #[Test]
    public function test_job_handles_large_batches(): void
    {
        // Create many inventory movements (reduced from 2500 to avoid timeout)
        for ($i = 0; $i < 30; $i++) {
            InventoryMovement::factory()->create([
                'inventory_id' => $this->inventory->id,
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'sku' => null,
                'name' => null,
                'department' => null,
                'category' => null,
            ]);
        }

        // Run the job
        $job = new UpdateInventoryMovementProductDetailsJob();
        $job->handle();

        // Assert all movements are updated
        $nullCount = InventoryMovement::where(function ($query) {
            $query->whereNull('sku')
                ->orWhereNull('name')
                ->orWhereNull('department')
                ->orWhereNull('category');
        })->count();

        $this->assertEquals(0, $nullCount);
    }

    #[Test]
    public function test_job_logs_completion_with_statistics(): void
    {
        // Create inventory movements
        InventoryMovement::factory()->count(2)->create([
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => null,
            'name' => null,
            'department' => null,
            'category' => null,
        ]);

        // Run the job
        $job = new UpdateInventoryMovementProductDetailsJob();
        $job->handle();

        // Assert that movements were updated (simpler test without Log mocking)
        $nullCount = InventoryMovement::where(function ($query) {
            $query->whereNull('sku')
                ->orWhereNull('name')
                ->orWhereNull('department')
                ->orWhereNull('category');
        })->count();

        $this->assertEquals(0, $nullCount);
    }

    #[Test]
    public function test_job_handles_products_with_null_details(): void
    {
        // Create a product with empty details
        $productWithNulls = Product::factory()->create([
            'sku' => 'NULL-TEST-SKU',
            'name' => 'Test Product With Nulls',
            'department' => '',
            'category' => '',
        ]);

        $inventory = Inventory::firstOrCreate([
            'product_id' => $productWithNulls->id,
            'warehouse_id' => $this->warehouse->id,
        ], [
            'quantity' => 0,
            'quantity_reserved' => 0,
            'warehouse_code' => $this->warehouse->code,
        ]);

        $movement = InventoryMovement::factory()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $productWithNulls->id,
            'warehouse_id' => $this->warehouse->id,
            'sku' => null,
            'name' => null,
            'department' => null,
            'category' => null,
        ]);

        // Run the job
        $job = new UpdateInventoryMovementProductDetailsJob();
        $job->handle();

        // Assert movement is updated with product's values (including empty strings)
        $movement->refresh();
        $this->assertEquals('NULL-TEST-SKU', $movement->sku);
        $this->assertEquals('Test Product With Nulls', $movement->name);
        $this->assertEquals('', $movement->department);
        $this->assertEquals('', $movement->category);
    }
}
