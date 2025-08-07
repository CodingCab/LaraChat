<?php

namespace Tests\Feature\Api\Reports\ProductsRequired;

use App\User;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Inventory;
use App\Models\Warehouse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();
    }

    #[Test]
    public function test_supplier_column_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();

        $columnNames = collect($response->json('meta.columns'))->pluck('name');
        $this->assertTrue($columnNames->contains('supplier'));
        $this->assertTrue($columnNames->contains('product_number'));
        $this->assertTrue($columnNames->contains('tags'));
    }

    #[Test]
    public function test_quantity_is_rounded_to_carton(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create(['code' => 'TWH']);
        $product = Product::factory()->create(['pack_quantity' => 24]);

        Inventory::query()->updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], [
            'product_sku' => $product->sku,
            'warehouse_code' => $warehouse->code,
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
            'reorder_point' => 0,
            'restock_level' => 30,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();

        $data = $response->json('data')[0];
        $this->assertEquals(48, $data['quantity_twh']);
    }

    #[Test]
    public function test_price_displayed_per_warehouse(): void
    {
        $user = User::factory()->create();

        $warehouseA = Warehouse::factory()->create(['code' => 'A']);
        $warehouseB = Warehouse::factory()->create(['code' => 'B']);
        $product = Product::factory()->create(['pack_quantity' => 5]);

        Inventory::query()->updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseA->id,
        ], [
            'product_sku' => $product->sku,
            'warehouse_code' => $warehouseA->code,
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
            'reorder_point' => 0,
            'restock_level' => 1,
        ]);

        Inventory::query()->updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseB->id,
        ], [
            'product_sku' => $product->sku,
            'warehouse_code' => $warehouseB->code,
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
            'reorder_point' => 0,
            'restock_level' => 1,
        ]);

        ProductPrice::query()->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseA->id)
            ->update(['price' => 4.5, 'cost' => 2.0]);

        ProductPrice::query()->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseB->id)
            ->update(['price' => 9.99, 'cost' => 3.0]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('sku', $product->sku);

        $this->assertEquals(4.5, $row['price_a']);
        $this->assertEquals(9.99, $row['price_b']);
    }

    #[Test]
    public function test_price_displayed_even_without_quantity(): void
    {
        $user = User::factory()->create();

        $warehouseA = Warehouse::factory()->create(['code' => 'A']);
        $warehouseB = Warehouse::factory()->create(['code' => 'B']);
        $product = Product::factory()->create(['pack_quantity' => 5]);

        Inventory::query()->updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseA->id,
        ], [
            'product_sku' => $product->sku,
            'warehouse_code' => $warehouseA->code,
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
            'reorder_point' => 0,
            'restock_level' => 1,
        ]);

        ProductPrice::query()->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseA->id)
            ->update(['price' => 3.5, 'cost' => 2.0]);

        ProductPrice::query()->where('product_id', $product->id)
            ->where('warehouse_id', $warehouseB->id)
            ->update(['price' => 8.0, 'cost' => 3.0]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('sku', $product->sku);

        $this->assertEquals(3.5, $row['price_a']);
        $this->assertEquals(8.0, $row['price_b']);
    }

    #[Test]
    public function test_tags_column_value(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create(['code' => 'T']);
        $product = Product::factory()->create(['pack_quantity' => 10]);
        $product->attachTags(['first', 'second']);

        Inventory::query()->updateOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], [
            'product_sku' => $product->sku,
            'warehouse_code' => $warehouse->code,
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
            'reorder_point' => 0,
            'restock_level' => 1,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/products-required');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('sku', $product->sku);
        $this->assertEquals('first, second', $row['tags']);
    }
}
