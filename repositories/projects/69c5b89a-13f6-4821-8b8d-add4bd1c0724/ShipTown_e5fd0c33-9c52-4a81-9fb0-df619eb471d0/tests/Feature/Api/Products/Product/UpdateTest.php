<?php

namespace Tests\Feature\Api\Products\Product;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Modules\SalesTaxes\src\Models\SaleTax;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/products';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $product = Product::factory()->create();

        $updateData = [
            'weight' => 10,
            'length' => 10.1,
            'width' => 10.12,
            'height' => 10.12,
            'pack_quantity' => 6,
            'product_number' => 'PN123',
        ];

        $response = $this->actingAs($user, 'api')->putJson($this->uri . "/$product->id", $updateData);

        $response->assertSuccessful();

        $responseData = $response->json('data');

        $this->assertEquals($updateData['weight'], $responseData['weight']);
        $this->assertEquals($updateData['length'], $responseData['length']);
        $this->assertEquals($updateData['width'], $responseData['width']);
        $this->assertEquals($updateData['height'], $responseData['height']);
        $this->assertEquals($updateData['pack_quantity'], $responseData['pack_quantity']);
        $this->assertEquals($updateData['product_number'], $responseData['product_number']);

        $product->refresh();

        $this->assertEquals($updateData['weight'], $product->weight);
        $this->assertEquals($updateData['length'], $product->length);
        $this->assertEquals($updateData['width'], $product->width);
        $this->assertEquals($updateData['height'], $product->height);
        $this->assertEquals($updateData['pack_quantity'], $product->pack_quantity);
        $this->assertEquals($updateData['product_number'], $product->product_number);
    }
}
