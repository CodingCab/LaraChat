<?php

namespace Tests\Feature\Api\Modules\Fakturowo\Configuration\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/fakturowo/configuration';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $config = FakturowoConfiguration::query()->create([
            'connection_code' => 'test_1',
            'api_key' => '123456789abcdefghijklmnop',
        ]);

        $response = $this->actingAs($user, 'api')->deleteJson($this->uri . '/' . $config->id);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseMissing('modules_fakturowo_configuration', [
            'id' => $config->id,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id'
            ],
        ]);
    }
}
