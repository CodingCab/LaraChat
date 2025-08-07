<?php

namespace Tests\Feature\Api\Products;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function testStoreNewProductReturnOk(): void
    {
        $salesTax = SaleTax::factory()->create();

        $params = [
            'sku' => 'TestSku',
            'name' => 'Product Name',
            'price' => 200,
            'type' => 'simple',
            'default_tax_code' => $salesTax->code
        ];

        $response = $this->post('api/products', $params);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'name',
            'price',
            'sale_price',
            'sale_price_start_date',
            'sale_price_end_date',
            'quantity',
            'quantity_reserved',
            'quantity_available',
            'updated_at',
            'created_at',
            'default_tax_code',
            'id'
        ]);
    }
}
