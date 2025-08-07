<?php

namespace Tests\Feature\Api\Reports\Heartbeats;

use App\Models\Heartbeat;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();
        Heartbeat::create([
            'code' => 'test-heartbeat',
            'expires_at' => now(),
            'error_message' => 'Error',
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/heartbeats');

        $response->assertOk();
    }
}
