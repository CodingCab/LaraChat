<?php

namespace Tests\Feature\Api\DataCollectorDiscounts;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/data-collector-discounts/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'code' => 'TEST_' . Str::random(8),
            'percentage_discount' => 10,
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_data_collector_discounts', ['id' => $response->json('data.id')]);
    }
}
