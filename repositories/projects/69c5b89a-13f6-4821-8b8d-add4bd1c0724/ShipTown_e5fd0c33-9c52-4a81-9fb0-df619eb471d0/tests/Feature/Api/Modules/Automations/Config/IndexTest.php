<?php

namespace Tests\Feature\Api\Modules\Automations\Config;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Automations\src\AutomationsServiceProvider;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'api');

        AutomationsServiceProvider::enableModule();
    }

    #[Test]
    public function test_get_config_call_returns_ok(): void
    {
        $response = $this->get(route('api.modules.automations.config.index'));

        ray($response->json());

        $response->assertOk();
        $response->assertJsonStructure([
            'description',
            'conditions' => [
                '*' => [
                    'class',
                    'description',
                ],
            ],
            'actions' => [
                '*' => [
                    'class',
                    'description',
                ],
            ],
        ]);
    }
}
