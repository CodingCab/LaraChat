<?php

namespace Tests\Feature\Api\OrdersStatuses;
use PHPUnit\Framework\Attributes\Test;

use App\Models\OrderStatus;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        OrderStatus::factory()->create();

        $response = $this->get('api/orders-statuses');

        $response->assertSuccessful();
    }
}
