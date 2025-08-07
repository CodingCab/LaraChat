<?php

namespace Tests\Feature\Api\Modules\PointOfSaleConfiguration;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/modules/point-of-sale-configuration';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(4, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                'id'
            ],
        ]);
    }
}
