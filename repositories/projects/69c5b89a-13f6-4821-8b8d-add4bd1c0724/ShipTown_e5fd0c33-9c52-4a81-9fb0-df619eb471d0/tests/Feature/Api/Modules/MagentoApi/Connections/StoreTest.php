<?php

namespace Tests\Feature\Api\Modules\MagentoApi\Connections;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function test_success_config_create(): void
    {
        if (env('MAGENTO_API_BASE_URL') === null) {
            $this->markTestSkipped('Magento 2 API Connection is not configured');
        }

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $warehouse = Warehouse::firstOrCreate(['code' => '999'], ['name' => '999']);

        $response = $this->actingAs($user, 'api')->json('post', route('api.modules.magento-api.connections.store'), [
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'tag' => 'some-tag',
            'pricing_source_warehouse_id' => $warehouse->id,
            'consumer_key' => 'some-token',
            'consumer_secret' => 'some-token',
            'api_access_token' => 'some-token',
            'access_token_secret' => 'some-token',
        ]);

        $response->assertSuccessful();
    }

    #[Test]
    public function test_failing_config_create(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->json('post', route('api.modules.magento-api.connections.store'), [
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'base_url',
        ]);
    }
}
