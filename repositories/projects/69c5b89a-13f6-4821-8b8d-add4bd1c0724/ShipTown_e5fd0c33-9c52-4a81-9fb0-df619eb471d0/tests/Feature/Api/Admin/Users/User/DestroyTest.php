<?php

namespace Tests\Feature\Api\Admin\Users\User;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use App\Modules\Permissions\src\Models\Permission;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::firstOrCreate(['name' => 'api.admin.users.update']));
        $admin->assignRole($adminRole);

        /** @var User $userToDelete */
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin, 'api')->delete('api/admin/users/'.$userToDelete->id);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id, 'deleted_at' => null]);
    }
}
