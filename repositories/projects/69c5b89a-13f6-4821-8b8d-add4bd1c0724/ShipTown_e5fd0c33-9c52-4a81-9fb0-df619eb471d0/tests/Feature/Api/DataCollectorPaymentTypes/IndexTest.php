<?php

namespace Tests\Feature\Api\DataCollectorPaymentTypes;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use App\Modules\Permissions\src\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/data-collector-payment-types';

    #[Test]
    public function testIfCallReturnsOk()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}
