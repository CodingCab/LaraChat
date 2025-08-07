<?php

namespace Tests\Feature\Api\DisassembleProducts;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected User $user;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        $this->product1 = Product::factory()->create();
        $this->product2 = Product::factory()->create();

        $inventory1 = $this->product1->inventory($this->user->warehouse_code)->first();
        $inventory1->quantity = 10;
        $inventory1->save();

        $inventory2 = $this->product2->inventory($this->user->warehouse_code)->first();
        $inventory2->quantity = 5;
        $inventory2->save();
    }

    #[Test]
    public function testIfCallReturnsOk()
    {
        $assemblyProduct = Product::factory()->create(['type' => 'assembly']);
        $assemblyProduct->inventory($this->user->warehouse_code)->first()->update(['quantity' => 1]);

        AssemblyProductsElement::query()->create([
            'assembly_product_id' => $assemblyProduct->id,
            'simple_product_id' => $this->product1->id,
            'required_quantity' => 3,
        ]);
        AssemblyProductsElement::query()->create([
            'assembly_product_id' => $assemblyProduct->id,
            'simple_product_id' => $this->product2->id,
            'required_quantity' => 1,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson(route('api.disassemble-products.store'), [
                'product_id' => $assemblyProduct->getKey(),
                'quantity' => 1,
            ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'adjustment',
            'product_id' => $this->product1->id,
            'quantity_delta' => 3,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'adjustment',
            'product_id' => $this->product2->id,
            'quantity_delta' => 1,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'type' => 'adjustment',
            'product_id' => $assemblyProduct->id,
            'quantity_delta' => -1,
        ]);
    }
}
