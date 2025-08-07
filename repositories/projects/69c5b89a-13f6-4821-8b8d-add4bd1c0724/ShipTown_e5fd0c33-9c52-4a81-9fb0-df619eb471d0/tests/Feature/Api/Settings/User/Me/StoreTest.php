<?php

namespace Tests\Feature\Api\Settings\User\Me;
use PHPUnit\Framework\Attributes\Test;

use App\User;
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
    public function test_store_call_returns_ok(): void
    {
        $params = [
            'name' => 'User Test',
            'printers' => [
                'shipping_labels_4x6' => 1
            ],
            'ask_for_shipping_number' => false,
        ];

        $response = $this->post(route('api.settings.user.me.store', $params));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'location_id',
                'printers',
                'address_label_template',
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
