<?php

namespace Tests\Feature\Api\AssemblyProductElements\AssemblyProductElement;

use App\Models\Product;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $productKit = Product::factory()->create(['type' => 'assembly']);
        $simpleProduct = Product::factory()->create();
        $assemblyProductElement = AssemblyProductsElement::query()->create([
            'assembly_product_id' => $productKit->id,
            'simple_product_id' => $simpleProduct->id,
            'required_quantity' => 2,
        ]);

        $response = $this->actingAs($user, 'api')
            ->delete(route('api.assembly-product-elements.destroy', $assemblyProductElement->getKey()));

        $response->assertOk();

        $this->assertDatabaseMissing('assembly_products_elements', ['id' => $assemblyProductElement->getKey()]);
    }
}
