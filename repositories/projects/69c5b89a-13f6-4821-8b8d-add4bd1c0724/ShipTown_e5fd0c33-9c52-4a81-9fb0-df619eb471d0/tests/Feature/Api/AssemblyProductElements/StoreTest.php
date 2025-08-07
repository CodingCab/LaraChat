<?php

namespace Tests\Feature\Api\AssemblyProductElements;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $productKit = Product::factory()->create(['type' => 'assembly']);
        $simpleProduct = Product::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.assembly-product-elements.store'), [
                'product_id' => $productKit->getKey(),
                'sku' => $simpleProduct->sku,
            ]);

        $response->assertSuccessful();

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
