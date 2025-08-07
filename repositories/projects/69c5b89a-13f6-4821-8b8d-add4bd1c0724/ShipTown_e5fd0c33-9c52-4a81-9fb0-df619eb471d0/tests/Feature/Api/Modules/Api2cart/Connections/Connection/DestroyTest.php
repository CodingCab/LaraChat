<?php

namespace Tests\Feature\Api\Modules\Api2cart\Connections\Connection;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin']));
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        $api2cart = Api2cartConnection::factory()->create();
        $response = $this->delete(route('api.modules.api2cart.connections.destroy', $api2cart));
        $response->assertStatus(200);
    }
}
