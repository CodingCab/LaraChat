<?php

namespace Tests\Feature\Api\Modules\Automations\Automation\Copy;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DefaultDisabledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_copied_automation_is_disabled(): void
    {
        $automation = Automation::factory()->create(['enabled' => true]);

        $response = $this->post(route('api.modules.automations.copy', $automation->id));

        $response->assertStatus(201);

        $copiedId = $response->json('data.id');
        $this->assertNotEquals($automation->id, $copiedId);
        $this->assertFalse(Automation::find($copiedId)->enabled);
    }
}
