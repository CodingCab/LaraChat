<?php

namespace Tests\Feature\Api\Modules\Autostatus\Picking\Configuration;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\AutoStatusRefill\src\Models\Automation;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $configuration = Automation::query()->make([
            'from_status_code' => 'processing',
            'to_status_code' => 'paid',
            'desired_order_count' => 2,
            'refill_only_at_0' => true,
        ]);

        $response = $this->postJson(route('api.modules.autostatus.picking.configuration.store'), $configuration->toArray());

        $response->assertSuccessful();
    }
}
