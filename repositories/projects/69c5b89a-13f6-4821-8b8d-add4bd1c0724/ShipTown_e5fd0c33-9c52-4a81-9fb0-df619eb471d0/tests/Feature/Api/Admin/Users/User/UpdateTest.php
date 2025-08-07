<?php

namespace Tests\Feature\Api\Admin\Users\User;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->forceDelete();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'user']);

        $warehouse = Warehouse::factory()->create();

        $data = [
            'name' => 'Test User',
            'role_id' => $role->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ];

        $response = $this->put(route('api.admin.users.update', $user), $data);

        $response->assertStatus(200);
    }
}
