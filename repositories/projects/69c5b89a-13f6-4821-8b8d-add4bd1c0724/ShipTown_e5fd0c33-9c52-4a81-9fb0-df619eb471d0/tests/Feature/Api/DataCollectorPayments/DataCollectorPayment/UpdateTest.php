<?php

namespace Tests\Feature\Api\DataCollectorPayments\DataCollectorPayment;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollectionPayment;
use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/data-collector-payments/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        /** @var DataCollectionPayment $paymentToUpdate */
        $paymentToUpdate = DataCollectionPayment::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson($this->uri.$paymentToUpdate->id, [
            'amount' => 100.99,
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseHas(
            'data_collection_payments',
            [
                'id' => $paymentToUpdate->id,
                'amount' => 100.99,
            ]
        );
    }
}
