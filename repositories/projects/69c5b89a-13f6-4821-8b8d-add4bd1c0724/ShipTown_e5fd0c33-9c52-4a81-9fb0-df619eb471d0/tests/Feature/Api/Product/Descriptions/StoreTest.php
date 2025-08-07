<?php

namespace Tests\Feature\Api\Product\Descriptions;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Models\ProductDescription;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/product/descriptions';

    #[Test]
    public function testCreateProductDescription()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $product->id,
            'language_code' => 'en',
            'description' => 'Product Description',
        ]);

        $response->assertStatus(201);

        $this->assertEquals(1, ProductDescription::where('product_id', $product->id)->where('language_code', 'en')->count());
    }

    public function testUpdateExsitingProductDescription()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $productDescription = ProductDescription::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $productDescription->product_id,
            'language_code' => 'en',
            'description' => 'Product Description Updated',
        ]);

        $response->assertStatus(200);

        $updated = ProductDescription::find($productDescription->id);

        $this->assertEquals('Product Description Updated', $updated->description);
    }
}
