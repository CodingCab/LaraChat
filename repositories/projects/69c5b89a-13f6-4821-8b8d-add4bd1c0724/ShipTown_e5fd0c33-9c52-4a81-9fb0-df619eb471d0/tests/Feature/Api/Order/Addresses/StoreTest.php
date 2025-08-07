<?php

namespace Tests\Feature\Api\Order\Addresses;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $response = $this->actingAs($user, 'api')->postJson(route('api.order.addresses.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'Mr.',
            'address1' => 'Main Street 123',
            'address2' => 'Apt. 123',
            'postcode' => '12345',
            'city' => 'New York',
            'country_code' => 'US',
            'country_name' => 'United States',
            'company' => 'Company Inc.',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders_addresses', ['id' => $response->json('data.id')]);
    }
}
