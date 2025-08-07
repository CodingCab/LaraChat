<?php

namespace Tests\Feature\Api\DataCollectorDiscounts\DataCollectorDiscount;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/data-collector-discounts/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        /** @var Discount $discount */
        $discount = Discount::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$discount->id);

        ray($response->json());

        $response->assertOk();

        $this->assertSoftDeleted('modules_data_collector_discounts', ['id' => $discount->id]);
    }
}
