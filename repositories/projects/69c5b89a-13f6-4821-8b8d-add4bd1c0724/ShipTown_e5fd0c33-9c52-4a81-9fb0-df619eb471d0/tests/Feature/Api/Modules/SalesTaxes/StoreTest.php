<?php

namespace Tests\Feature\Api\Modules\SalesTaxes;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/modules/sales-taxes/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($role);

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'code' => 'VAT_10',
            'rate' => 10,
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_sale_taxes', ['id' => $response->json('data.id')]);
    }
}
