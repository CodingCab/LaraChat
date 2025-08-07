<?php

namespace Tests\Feature\Api\Modules\Automations\Automation\Copy;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReturnsRelationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_copy_response_contains_conditions_and_actions(): void
    {
        $automation = Automation::factory()->create();
        $automation->conditions()->create([
            'condition_class' => \App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition::class,
            'condition_value' => 'paid',
        ]);
        $automation->actions()->create([
            'priority' => 1,
            'action_class' => \App\Modules\Automations\src\Actions\Order\SetStatusCodeAction::class,
            'action_value' => 'store_pickup',
        ]);

        $response = $this->post(route('api.modules.automations.copy', $automation->id));

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('data.conditions'));
        $this->assertNotEmpty($response->json('data.actions'));
    }
}
