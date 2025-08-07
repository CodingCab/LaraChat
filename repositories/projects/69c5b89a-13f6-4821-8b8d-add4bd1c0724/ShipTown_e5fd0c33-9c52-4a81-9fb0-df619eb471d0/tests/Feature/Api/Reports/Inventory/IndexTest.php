<?php

namespace Tests\Feature\Api\Reports\Inventory;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_pagination_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Warehouse::factory()->create();
        Product::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/inventory?page=1&per_page=2');

        $response->assertOk();
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Warehouse::factory()->create();
        Product::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/inventory');

        $response->assertOk();
    }
}
