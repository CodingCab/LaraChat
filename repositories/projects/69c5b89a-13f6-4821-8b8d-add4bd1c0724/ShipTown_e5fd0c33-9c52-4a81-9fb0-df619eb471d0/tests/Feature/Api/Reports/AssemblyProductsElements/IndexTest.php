<?php

namespace Tests\Feature\Api\Reports\AssemblyProductsElements;

use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;
use App\User;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_pagination_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $assemblyProduct = Product::factory()->create(['type' => 'assembly']);
        $simpleProduct = Product::factory()->create();

        AssemblyProductsElement::factory()->count(2)->create([
            'assembly_product_id' => $assemblyProduct->id,
            'simple_product_id' => $simpleProduct->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/assembly-products-elements?page=1&per_page=1');

        $response->assertOk();
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $assemblyProduct = Product::factory()->create(['type' => 'assembly']);
        $simpleProduct = Product::factory()->create();

        AssemblyProductsElement::factory()->create([
            'assembly_product_id' => $assemblyProduct->id,
            'simple_product_id' => $simpleProduct->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/assembly-products-elements');

        $response->assertOk();
    }
}

