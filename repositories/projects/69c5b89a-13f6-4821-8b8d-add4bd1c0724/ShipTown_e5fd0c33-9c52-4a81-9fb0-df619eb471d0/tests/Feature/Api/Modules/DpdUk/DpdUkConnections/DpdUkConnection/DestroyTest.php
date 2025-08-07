<?php

namespace Tests\Feature\Api\Modules\DpdUk\DpdUkConnections\DpdUkConnection;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/dpd-uk/dpd-uk-connections/';

    #[Test]
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = \App\Modules\DpdUk\src\Models\Connection::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$connection->getKey());

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}
