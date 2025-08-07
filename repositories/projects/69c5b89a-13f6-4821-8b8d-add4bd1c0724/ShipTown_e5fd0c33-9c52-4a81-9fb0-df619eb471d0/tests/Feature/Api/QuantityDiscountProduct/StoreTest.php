<?php

namespace Tests\Feature\Api\QuantityDiscountProduct;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/quantity-discount-product/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        /** @var QuantityDiscount $discount */
        $discount = QuantityDiscount::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'quantity_discount_id' => $discount->id,
            'product_id' => $product->id,
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseHas('modules_quantity_discounts_products', [
            'quantity_discount_id' => $discount->id,
            'product_id' => $product->id,
        ]);
    }
}
