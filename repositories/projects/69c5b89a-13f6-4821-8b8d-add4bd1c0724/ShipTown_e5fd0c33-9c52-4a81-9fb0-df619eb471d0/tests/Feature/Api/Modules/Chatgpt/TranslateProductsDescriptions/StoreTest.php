<?php

namespace Tests\Feature\Api\Modules\Chatgpt\TranslateProductsDescriptions;
use PHPUnit\Framework\Attributes\Test;

use App\Models\ProductDescription;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/modules/chatgpt/translate-products-descriptions';

    #[Test]
    public function testIfCallReturnsOk()
    {
        if (!env('OPENAI_API_KEY')) {
            $this->markTestSkipped('OPENAI_API_KEY is not set in .env file');
            return;
        }

        $user = User::factory()->create();
        $user->assignRole('admin');
        $productDescription = ProductDescription::factory()->create([
            'description' => 'Best product ever'
        ]);

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_description_id' => $productDescription->id,
            'auto_translate_to' => ['es', 'it']
        ]);

        $response->assertStatus(200);

        $updated = ProductDescription::find($productDescription->id);

        $countDescriptions = ProductDescription::where('product_id', $productDescription->product_id)
            ->whereIn('language_code', ['es', 'it', 'en'])
            ->count();

        $this->assertEquals($countDescriptions, 3);
    }
}
