<?php

namespace Tests\Feature\Api\Modules\MagentoApi\Connections\Connection;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Magento2API\PriceSync\src\Models\MagentoConnection;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'api_access_token' => 'some-token',
        ]);

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->delete(route('api.modules.magento-api.connections.destroy', $connection));

        $response->assertOk();

        $this->assertFalse(MagentoConnection::where('id', $connection->id)->exists());
    }
}
