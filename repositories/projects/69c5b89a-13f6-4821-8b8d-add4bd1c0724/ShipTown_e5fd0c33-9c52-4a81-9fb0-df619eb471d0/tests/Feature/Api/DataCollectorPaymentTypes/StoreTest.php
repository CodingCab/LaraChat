<?php

namespace Tests\Feature\Api\DataCollectorPaymentTypes;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Faker\Factory as Faker;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/data-collector-payment-types/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $faker = Faker::create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'name' => $faker->word,
            'code' => strtoupper($faker->unique()->word),
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('payment_types', ['id' => $response->json('data.id')]);
    }
}
