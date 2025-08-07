<?php

namespace Tests\Feature\Api\Settings\Modules\Automations\Run;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('user');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $automation = Automation::factory()->create();

        $response = $this->post(route('api.settings.modules.automations.run.store'), [
            'automation_id' => $automation->getKey(),
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'automation_id',
                'time',
            ],
        ]);
    }
}
