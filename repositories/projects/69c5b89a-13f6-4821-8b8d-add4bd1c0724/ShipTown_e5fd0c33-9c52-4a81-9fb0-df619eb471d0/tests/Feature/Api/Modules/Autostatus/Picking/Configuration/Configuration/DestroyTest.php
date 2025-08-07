<?php

namespace Tests\Feature\Api\Modules\Autostatus\Picking\Configuration\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\AutoStatusRefill\src\Models\Automation;
use App\Modules\Permissions\src\Models\Permission;
use App\Modules\Permissions\src\Models\Role;
use App\User;
use Tests\ResetsDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use ResetsDatabase;

    private string $uri = '/api/modules/autostatus/picking/configuration/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $permission = Permission::query()->firstOrCreate([
            'name' => 'api.modules.autostatus.picking.configuration.destroy',
        ]);

        Role::firstOrCreate(['name' => 'admin'],[])->givePermissionTo($permission);

        $user->assignRole('admin');

        $automation = Automation::query()
            ->create([
                'from_status_code' => 'processing',
                'to_status_code' => 'paid',
                'desired_order_count' => 2,
                'refill_only_at_0' => true,
            ]);

        $response = $this->actingAs($user, 'api')->delete($this->uri . $automation->getKey());

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id'
                ],
            ],
        ]);
    }
}
