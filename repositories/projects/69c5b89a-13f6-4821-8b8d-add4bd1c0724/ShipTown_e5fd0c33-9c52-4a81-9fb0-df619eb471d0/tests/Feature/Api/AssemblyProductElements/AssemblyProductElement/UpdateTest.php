<?php

namespace Tests\Feature\Api\AssemblyProductElements\AssemblyProductElement;

use App\Models\Product;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function testIfCallReturnsOk()
    {
        /** @var User $user * */
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
            ->putJson(route('api.assembly-product-elements.update', $assemblyProductElement->getKey()),
                [
                    'quantity' => 3,
                ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'assembly_product_id',
                'simple_product_id',
                'required_quantity'
            ],
        ]);
    }
}
