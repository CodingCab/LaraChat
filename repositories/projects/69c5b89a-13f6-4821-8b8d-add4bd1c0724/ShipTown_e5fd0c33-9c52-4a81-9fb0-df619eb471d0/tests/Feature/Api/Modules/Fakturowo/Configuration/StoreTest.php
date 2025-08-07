<?php

namespace Tests\Feature\Api\Modules\Fakturowo\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/modules/fakturowo/configuration';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'connection_code' => 'test_1',
            'api_key' => '123456789abcdefghijklmnop',
            'api_url' => 'https://example.test/api',
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_fakturowo_configuration', [
            'connection_code' => 'test_1',
            'api_url' => 'https://example.test/api',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id'
            ],
        ]);
    }
}
