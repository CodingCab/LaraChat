<?php

namespace Tests\Feature\Api\Modules\ActiveOrdersInventoryReservations\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/modules/active-orders-inventory-reservations/configuration';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    #[Test]
    public function testUserAccess(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        $response->assertForbidden();
    }
}
