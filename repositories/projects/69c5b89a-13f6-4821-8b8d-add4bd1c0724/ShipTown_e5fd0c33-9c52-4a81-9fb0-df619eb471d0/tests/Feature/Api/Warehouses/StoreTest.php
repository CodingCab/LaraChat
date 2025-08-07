<?php

namespace Tests\Feature\Api\Warehouses;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
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
    public function test_store_call_returns_ok(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->make();

        $data = [
            'name' => $warehouse->name,
            'code' => $warehouse->code,
        ];

        $response = $this->post(route('api.warehouses.index'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'code',
            ],
        ]);
    }
}
