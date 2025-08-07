<?php

namespace Tests\Feature\Api\Admin\User\Roles;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/admin/user/roles';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $roleName = 'test_' . uniqid();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'name' => $roleName
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('roles', ['name' => $roleName]);
    }
}
