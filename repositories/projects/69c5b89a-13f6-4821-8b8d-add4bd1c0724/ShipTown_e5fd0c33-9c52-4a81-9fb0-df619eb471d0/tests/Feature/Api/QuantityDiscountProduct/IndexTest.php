<?php

namespace Tests\Feature\Api\QuantityDiscountProduct;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/quantity-discount-product';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        QuantityDiscountsProduct::factory()->count(5)->create();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        ray($response->json());

        $response->assertSuccessful();

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
        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        $response->assertSuccessful();
    }
}
