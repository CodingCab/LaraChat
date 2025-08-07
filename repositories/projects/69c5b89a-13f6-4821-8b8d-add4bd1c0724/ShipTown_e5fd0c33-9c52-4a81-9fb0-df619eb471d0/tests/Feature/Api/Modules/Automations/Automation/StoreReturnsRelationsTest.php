<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;

use App\User;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StoreReturnsRelationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_response_contains_conditions_and_actions(): void
    {
        $data = [
            'name' => 'Test Automation',
            'enabled' => true,
            'description' => 'Some description',
            'priority' => 1,
            'conditions' => [
                [
                    'condition_class' => CanFulfillFromLocationCondition::class,
                    'condition_value' => 'paid',
                ],
                [
                    'condition_class' => ShippingMethodCodeEqualsCondition::class,
                    'condition_value' => 'paid',
                ],
            ],
            'actions' => [
                [
                    'priority' => 1,
                    'action_class' => SetStatusCodeAction::class,
                    'action_value' => 'store_pickup',
                ],
            ],
        ];

        $response = $this->post(route('api.modules.automations.store'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'conditions',
                'actions',
            ],
        ]);
        $this->assertCount(2, $response->json('data.conditions'));
        $this->assertCount(1, $response->json('data.actions'));
    }
}
