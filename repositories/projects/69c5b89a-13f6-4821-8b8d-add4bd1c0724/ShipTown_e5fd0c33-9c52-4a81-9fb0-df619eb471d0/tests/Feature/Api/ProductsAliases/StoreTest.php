<?php

namespace Tests\Feature\Api\ProductsAliases;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/products-aliases';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $product->id,
            'alias' => fake()->ean8(),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    #[Test]
    public function testUserAccess(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->postJson($this->uri, []);

        $response->assertForbidden();
    }
}
