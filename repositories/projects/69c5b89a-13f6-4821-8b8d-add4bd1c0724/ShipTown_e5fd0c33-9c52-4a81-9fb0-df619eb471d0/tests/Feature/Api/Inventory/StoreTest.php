<?php

namespace Tests\Feature\Api\Inventory;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllInventoryRecordsExistsJob;
use Laravel\Passport\Passport;
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
        $admin = auth()->user();

        Product::factory()->create();

        $inventory = Inventory::query()
            ->where('warehouse_code', $admin->warehouse_code)
            ->first();

        $params = [
            'id' => $inventory->getKey(),
            'shelve_location' => 'test',
        ];

        $response = $this->post(url('api/inventory'), $params);

        ray($response->json());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'quantity',
                    'quantity_reserved',
                    'product_id',
                    'warehouse_code',
                    'updated_at',
                    'created_at',
                    'id',
                    'quantity_available',
                ],
            ],
        ]);
    }

    public function testIfCantPostWithoutData(): void
    {
        $response = $this->postJson(url('api/inventory'), []);

        $response->assertStatus(422);
    }

    public function testQuantityUpdate(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        Product::factory()->create();

        $inventory = Inventory::query()
            ->where('warehouse_code', $user->warehouse_code)
            ->first();

        $update = [
            'id' => $inventory->getKey(),
            'quantity' => rand(100, 200),
            'quantity_reserved' => rand(10, 50),
        ];

        $response = $this->postJson(route('api.inventory.store'), $update);

        $response->assertStatus(200);
    }

    public function test_cannot_update_inventory_from_other_warehouse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $otherWarehouse = Warehouse::factory()->create();
        Product::factory()->create();
        EnsureAllInventoryRecordsExistsJob::dispatchSync();

        $inventory = Inventory::query()
            ->where('warehouse_code', $otherWarehouse->code)
            ->first();

        $response = $this->postJson(route('api.inventory.store'), [
            'id' => $inventory->getKey(),
            'shelve_location' => 'TEST',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function test_user_without_warehouse_code_can_update(): void
    {
        $user = User::factory()->create(['warehouse_code' => null]);

        $this->actingAs($user, 'api');

        Product::factory()->create();

        $inventory = Inventory::query()->first();

        $response = $this->postJson(route('api.inventory.store'), [
            'id' => $inventory->getKey(),
            'shelve_location' => 'TEST',
        ]);

        $response->assertOk();
    }
}
