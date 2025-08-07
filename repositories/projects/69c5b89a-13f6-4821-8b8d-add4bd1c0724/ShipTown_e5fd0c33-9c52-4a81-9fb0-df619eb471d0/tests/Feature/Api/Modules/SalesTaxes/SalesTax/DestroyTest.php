<?php

namespace Tests\Feature\Api\Modules\SalesTaxes\SalesTax;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/sales-taxes/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        /** @var SaleTax $saleTax */
        $saleTax = SaleTax::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$saleTax->id);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertSoftDeleted('modules_sale_taxes', ['id' => $saleTax->id]);
    }
}
