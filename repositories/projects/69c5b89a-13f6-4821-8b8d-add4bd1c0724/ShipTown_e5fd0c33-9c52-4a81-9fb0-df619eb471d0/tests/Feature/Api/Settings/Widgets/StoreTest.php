<?php

namespace Tests\Feature\Api\Settings\Widgets;
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
        $response = $this->post(route('api.settings.widgets.store'), [
            'name' => 'Tes widget',
            'config' => [],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'name',
                'config' => [],
                'id',
            ],
        ]);
    }
}
