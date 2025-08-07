<?php

namespace Tests\Feature\Api\CsvImport\Products;

use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/csv-import/products';

    public function setUp(): void
    {
        parent::setUp();
        CsvProductImport::factory()->create();
    }

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson(route('api.csv-import.products.index'));

        $response->assertSuccessful();

        $this->assertGreaterThan(0, count($response->json('data')), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sku',
                    'name',
                    'department',
                    'category',
                    'weight',
                    'length',
                    'height',
                    'width',
                    'alias',
                    'tags_add',
                    'tags_remove',
                    'price',
                    'sale_price',
                    'sale_price_start_date',
                    'sale_price_end_date',
                    'commodity_code',
                    'sales_tax_code',
                    'supplier',
                    'processed_at',
                ],
            ],
            'meta'
        ]);
    }
}
