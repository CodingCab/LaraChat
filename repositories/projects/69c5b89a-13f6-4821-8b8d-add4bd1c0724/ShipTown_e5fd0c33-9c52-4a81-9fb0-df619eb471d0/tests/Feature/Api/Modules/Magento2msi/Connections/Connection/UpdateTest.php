<?php

namespace Tests\Feature\Api\Modules\Magento2msi\Connections\Connection;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnection;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/modules/magento2msi/connections/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $connection = Magento2msiConnection::factory()->create();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->putJson($this->uri.$connection->getKey(), ['magento_source_code' => 'new_magento_source_code']);

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
        $response = $this->actingAs($user, 'api')->putJson($this->uri.$connection->getKey(), []);

        $response->assertForbidden();
    }
}
