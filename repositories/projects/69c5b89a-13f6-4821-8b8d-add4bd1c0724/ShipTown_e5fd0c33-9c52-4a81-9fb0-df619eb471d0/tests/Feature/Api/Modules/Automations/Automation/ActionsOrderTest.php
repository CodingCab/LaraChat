<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition;
use App\Modules\Automations\src\Models\Automation;
use App\User;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ActionsOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function actions_are_returned_in_priority_order(): void
    {
        $data = [
            'name' => 'Test Automation',
            'enabled' => true,
            'priority' => 1,
            'conditions' => [
                [
                    'condition_class' => CanFulfillFromLocationCondition::class,
                    'condition_value' => 'paid',
                ],
            ],
            'actions' => [
                [
                    'priority' => 2,
                    'action_class' => SetStatusCodeAction::class,
                    'action_value' => 'A',
                ],
                [
                    'priority' => 1,
                    'action_class' => SetStatusCodeAction::class,
                    'action_value' => 'B',
                ],
            ],
        ];

        $response = $this->post(route('api.modules.automations.store'), $data);
        $response->assertStatus(201);

        $automationId = $response->json('data.id');
        $showResponse = $this->get(route('api.modules.automations.show', $automationId));
        $showResponse->assertStatus(200);

        $actions = $showResponse->json('data.actions');
        $this->assertSame(['A', 'B'], array_column($actions, 'action_value'));
    }
}
