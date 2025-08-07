<?php

namespace Tests\Feature\Api\Modules\Permissions\BulkUpdate;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use App\Modules\Permissions\src\Models\Permission;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/modules/permissions/bulk-update';

    protected User $user;

    protected Role $adminRole;

    protected Role $userRole;

    protected Permission $permission;

    protected array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->userRole = Role::firstOrCreate(['name' => 'user']);

        $this->user = User::factory()->create();
        $this->user->assignRole($this->adminRole);
        $this->permission = Permission::firstOrCreate(['name' => 'api.activities.index']);

        $this->permissions = [
            $this->adminRole->id => [
                $this->permission->id,
            ],
            $this->userRole->id => [
                $this->permission->id,
            ],
        ];
    }

    #[Test]
    public function testIfCallReturnsOk()
    {
        $response = $this->actingAs($this->user, 'api')->putJson($this->uri, [
            'permissions' => json_encode($this->permissions),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('users_roles_permissions', [
            'role_id' => $this->adminRole->id,
            'permission_id' => $this->permission->id,
        ]);
    }
}
