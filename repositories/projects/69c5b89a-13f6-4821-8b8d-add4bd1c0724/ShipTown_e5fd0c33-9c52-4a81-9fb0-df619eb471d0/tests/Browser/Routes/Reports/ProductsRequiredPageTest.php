<?php

namespace Tests\Browser\Routes\Reports;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductsRequiredPageTest extends DuskTestCase
{
    private string $uri = '/reports/products-required';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->visit($this->uri);
    }

    public function testProductsRequiredReport(): void
    {
        // Create user
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        // Create test data
        $warehouse = Warehouse::factory()->create(['code' => 'TST']);
        $product = Product::factory()->create([
            'sku' => 'TEST-SKU-001',
            'name' => 'Test Product',
            'pack_quantity' => 24,
            'product_number' => 'PN12345'
        ]);
        
        // Create inventory with required quantity using restock_level
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
            'restock_level' => 30, // This will generate quantity_required of 30
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit($this->uri)
                ->pause(1000) // Wait for page to load
                ->assertPathIs($this->uri);
        });
    }
}
