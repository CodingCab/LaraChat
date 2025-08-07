<?php

namespace Tests\Feature\Api\Modules\PointOfSaleConfiguration\PointOfSaleConfiguration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\PointOfSaleConfiguration\src\Models\PointOfSaleConfiguration;
use App\Modules\PointOfSaleConfiguration\src\PointOfSaleConfigurationServiceProvider;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = '/api/modules/point-of-sale-configuration/';

    private PointOfSaleConfiguration $pointOfSaleConfiguration;

    protected function setUp(): void
    {
        parent::setUp();

        PointOfSaleConfigurationServiceProvider::enableModule();

        $this->pointOfSaleConfiguration = PointOfSaleConfiguration::firstOrCreate([
            'next_transaction_number' => 1,
        ]);
    }

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->putJson($this->uri.$this->pointOfSaleConfiguration->getKey(), [
            'next_transaction_number' => 123,
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_point_of_sale_configuration', [
            'next_transaction_number' => 123,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id'
            ],
        ]);
    }
}
