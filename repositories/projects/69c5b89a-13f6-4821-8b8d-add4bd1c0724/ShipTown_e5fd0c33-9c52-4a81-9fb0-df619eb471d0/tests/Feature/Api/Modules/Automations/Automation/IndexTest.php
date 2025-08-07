<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;
use PHPUnit\Framework\Attributes\Test;

use App\Events\Order\OrderCreatedEvent;
use App\Modules\Automations\src\Models\Automation;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_show_call_returns_ok(): void
    {
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
            'event_class' => OrderCreatedEvent::class,
        ]);

        $response = $this->get(route('api.modules.automations.index', [
            'include' => 'actions,conditions',
        ]));

        ray($response->json());

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'enabled',
                    //                    'actions',
                    //                    'conditions',
                ],
            ],
        ]);
    }
}
