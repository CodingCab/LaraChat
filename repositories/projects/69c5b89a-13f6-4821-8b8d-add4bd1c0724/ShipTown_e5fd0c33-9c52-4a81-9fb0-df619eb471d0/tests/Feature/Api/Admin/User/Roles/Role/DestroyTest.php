<?php

namespace Tests\Feature\Api\Admin\User\Roles\Role;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Permissions\src\Models\Role;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/admin/user/roles';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $role = Role::firstOrCreate(['name' => 'test']);

        $response = $this->actingAs($user, 'api')->deleteJson($this->uri . '/' . $role->id);

        $response->assertOk();
    }
}
