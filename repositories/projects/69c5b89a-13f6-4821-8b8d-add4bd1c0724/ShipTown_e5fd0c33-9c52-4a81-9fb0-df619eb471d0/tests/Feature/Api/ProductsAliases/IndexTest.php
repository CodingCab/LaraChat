<?php

namespace Tests\Feature\Api\ProductsAliases;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = '/api/products-aliases';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        Product::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }

    #[Test]
    public function testUserAccess(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->get($this->uri);

        $response->assertSuccessful();
    }
}
