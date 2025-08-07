<?php

namespace Tests\Feature\Api\Admin\Users\User;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_show_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson(route('api.admin.users.show', $user->id));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }
}
