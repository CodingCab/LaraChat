<?php

namespace Tests\Feature\Api\ProductsAliases\ProductsAlias;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Models\ProductAlias;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = '/api/products-aliases';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $productAlias = ProductAlias::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson($this->uri . '/' . $productAlias->id, [
            'quantity' => 10,
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'quantity',
                'product_id',
            ],
        ]);

        $this->assertEquals($productAlias->id, $response->json('data.id'));
        $this->assertEquals($productAlias->product_id, $response->json('data.product_id'));
        $this->assertEquals(10, $response->json('data.quantity'));
    }

    #[Test]
    public function testUserAccess()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->postJson($this->uri, []);

        $response->assertForbidden();
    }
}
