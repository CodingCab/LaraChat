<?php

namespace Tests\Feature\Api\Settings\Widgets\Widget;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Widget;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
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
        $widget = Widget::create(['name' => 'testing', 'config' => []]);

        $response = $this->put(route('api.settings.widgets.update', $widget), [
            'name' => 'Tes widget',
            'config' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'name',
                'config' => [],
                'id',
            ],
        ]);
    }
}
