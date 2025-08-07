<?php

namespace Tests\Feature\Api\Modules\Automations;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    private string $uri = '/api/modules/automations/config';

    #[Test]
    public function test_config_endpoint_returns_lists(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        $response->assertOk();
        $response->assertJsonStructure([
            'conditions',
            'actions',
        ]);
    }
}
