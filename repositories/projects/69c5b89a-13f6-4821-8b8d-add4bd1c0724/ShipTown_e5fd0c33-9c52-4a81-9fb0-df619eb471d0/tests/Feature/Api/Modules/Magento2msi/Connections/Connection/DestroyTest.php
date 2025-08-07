<?php

namespace Tests\Feature\Api\Modules\Magento2msi\Connections\Connection;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnection;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/magento2msi/connections/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = Magento2msiConnection::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$connection->getKey());

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
        $connection = Magento2msiConnection::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson($this->uri.$connection->getKey(), []);

        ray($response->json());

        $response->assertForbidden();
    }
}
