<?php

namespace Tests\Feature\Api\QuantityDiscounts\QuantityDiscount;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/quantity-discounts/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        /** @var QuantityDiscount $discountToDelete */
        $discountToDelete = QuantityDiscount::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri . $discountToDelete->id);

        ray($response->json());

        $response->assertOk();

        $this->assertSoftDeleted('modules_quantity_discounts', ['id' => $discountToDelete->id]);
    }
}
