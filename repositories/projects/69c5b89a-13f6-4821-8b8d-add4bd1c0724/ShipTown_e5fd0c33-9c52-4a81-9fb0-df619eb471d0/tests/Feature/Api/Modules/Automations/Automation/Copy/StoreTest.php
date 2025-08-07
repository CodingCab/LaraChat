<?php

namespace Tests\Feature\Api\Modules\Automations\Automation\Copy;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_copy_call_returns_created(): void
    {
        $automation = Automation::factory()->create();
        $beforeCount = Automation::count();

        $response = $this->post(route('api.modules.automations.copy', $automation->id));

        $response->assertStatus(201);
        $this->assertDatabaseCount('modules_automations', $beforeCount + 1);
        $this->assertDatabaseHas('modules_automations', [
            'name' => $automation->name.' copy',
        ]);
    }
}
