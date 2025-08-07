<?php

namespace Tests\Feature\Api\Product\Tags;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\User;
use Database\Seeders\ProductTagsSeeder;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/product/tags';

    #[Test]
    public function testCreateProductDescription()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $product->id,
            'tags' => ['tags1', 'tags2'],
        ]);

        $response->assertOk();
        $product->refresh();

        $this->assertCount(2, $product->tags);
    }
}
