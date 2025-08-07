<?php

namespace Tests\Feature\Api\Warehouses\Warehouse;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_update_call_returns_ok(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Warehouse $newWarehouse */
        $newWarehouse = Warehouse::factory()->make();

        $data = [
            'name' => $newWarehouse->name,
            'code' => $newWarehouse->code,
            'tags' => ['tag1', 'tag2'],
        ];

        $response = $this->put(route('api.warehouses.update', $warehouse), $data);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'code',
            ],
        ]);

        $updatedWarehouse = Warehouse::find($warehouse->id);
        $this->assertEquals($updatedWarehouse->name, $newWarehouse->name);
        $this->assertEquals($updatedWarehouse->code, $newWarehouse->code);
    }
}
