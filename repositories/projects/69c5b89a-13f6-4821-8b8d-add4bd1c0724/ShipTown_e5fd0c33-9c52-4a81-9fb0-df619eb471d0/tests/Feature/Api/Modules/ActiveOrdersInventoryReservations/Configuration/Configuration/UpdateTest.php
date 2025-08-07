<?php

namespace Tests\Feature\Api\Modules\ActiveOrdersInventoryReservations\Configuration\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/modules/active-orders-inventory-reservations/configuration/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $warehouse = Warehouse::factory()->create();
        $configuration = Configuration::query()->firstOrCreate();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $fullUrl = $this->uri.$configuration->getKey();

        $response = $this->actingAs($user, 'api')
            ->putJson($fullUrl, [
                'warehouse_id' => $warehouse->getKey(),
            ]);

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
        $configuration = Configuration::query()->firstOrCreate();
        $fullUrl = $this->uri.$configuration->getKey();

        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->putJson($fullUrl, []);

        ray($response->json());

        $response->assertForbidden();
    }
}
