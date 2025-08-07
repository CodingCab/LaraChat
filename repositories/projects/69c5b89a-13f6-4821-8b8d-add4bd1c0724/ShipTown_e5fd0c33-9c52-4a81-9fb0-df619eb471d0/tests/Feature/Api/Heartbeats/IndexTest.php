<?php

namespace Tests\Feature\Api\Heartbeats;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Heartbeat;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        // add expired heartbeat
        Heartbeat::updateOrCreate(
            [
                'code' => 'somealert',
            ],
            [
                'expires_at' => now()->subMinutes(10),
                'error_message' => 'Some error message',
                'auto_heal_job_class' => \App\Jobs\DispatchEveryDayEventJob::class,
            ]
        );

        $response = $this->actingAs($user, 'api')->getJson(route('api.heartbeats.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'code',
                    'expires_at',
                    'error_message',
                    'auto_heal_job_class',
                ],
            ],
        ]);
        $response->assertJsonFragment(['code' => 'somealert']);
    }
}
