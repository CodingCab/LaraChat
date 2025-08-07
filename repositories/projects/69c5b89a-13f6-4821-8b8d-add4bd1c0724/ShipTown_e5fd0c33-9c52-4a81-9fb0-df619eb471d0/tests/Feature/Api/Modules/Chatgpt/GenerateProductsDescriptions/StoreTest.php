<?php

namespace Tests\Feature\Api\Modules\Chatgpt\GenerateProductsDescriptions;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/modules/chatgpt/generate-products-descriptions';

    #[Test]
    public function testIfCallReturnsOk()
    {
        if (!env('OPENAI_API_KEY')) {
            $this->markTestSkipped('OPENAI_API_KEY is not set in .env file');
            return;
        }

        $user = User::factory()->create();
        $user->assignRole('admin');

        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $product->id,
            'language_code' => 'en'
        ]);

        $response->assertSuccessful();
    }
}
