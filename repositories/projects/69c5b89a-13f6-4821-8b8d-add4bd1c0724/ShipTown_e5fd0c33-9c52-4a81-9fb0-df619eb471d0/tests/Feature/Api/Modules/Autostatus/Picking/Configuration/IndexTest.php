<?php

namespace Tests\Feature\Api\Modules\Autostatus\Picking\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Permissions\src\Models\Permission;
use App\Modules\Permissions\src\Models\Role;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $permission = Permission::query()->firstOrCreate([
            'name' => 'api.modules.autostatus.picking.configuration.index',
        ]);

        Role::firstOrCreate(['name' => 'admin'],[])->givePermissionTo($permission);

        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $response = $this->get(route('api.modules.autostatus.picking.configuration.index'));

        $response->assertSuccessful();
    }
}
