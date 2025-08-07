<?php

namespace Tests\Feature\Api\Admin\Users;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $response = $this->actingAs($user, 'api')
            ->getJson(route('api.admin.users.index', ['include' => 'roles']));

        $response->assertOk();

        $this->assertGreaterThan(0, $response->json('meta.total'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'printers',
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
