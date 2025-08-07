<?php

namespace Tests\Feature\Api\Admin\Users;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::factory()->create();
        $roles = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($roles);
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $warehouse = Warehouse::factory()->create();
        $testData = User::factory()->make()->toArray();
        $testData['role_id'] = Role::firstOrCreate(['name' => 'user'])->id;

        $response = $this->postJson(route('api.admin.users.store'), $testData);

        $response->assertStatus(201);
    }

    public function test_add_deleted_user_return_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->delete();

        $response = $this->post(route('api.admin.users.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => Role::firstOrCreate(['name' => 'user'])->id,
        ]);

        $response->assertStatus(200);
    }
}
