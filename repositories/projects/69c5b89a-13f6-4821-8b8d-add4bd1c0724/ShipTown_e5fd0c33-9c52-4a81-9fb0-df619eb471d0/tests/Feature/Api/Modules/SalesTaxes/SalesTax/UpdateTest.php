<?php

namespace Tests\Feature\Api\Modules\SalesTaxes\SalesTax;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
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

        $response = $this->actingAs($user, 'api')->putJson($this->uri.$saleTax->id, [
            'code' => 'VAT_10',
            'rate' => 10,
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_sale_taxes', [
            'id' => $saleTax->id,
            'code' => 'VAT_10',
            'rate' => 10,
        ]);
    }
}
