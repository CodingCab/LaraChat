<?php

namespace Tests\Feature\Api\Modules\RmsApi\Connections;
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
            'location_id' => rand(1, 99),
            'url' => 'https://demo.rmsapi.products.management',
            'username' => 'demo@products.management',
            'password' => 'secret123',
        ];

        $response = $this->post(route('api.modules.rms_api.connections.store'), $params);

        $response->assertStatus(201);
    }
}
