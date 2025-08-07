<?php

namespace Tests\Feature\Api\DataCollector\Comments;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->get('api/data-collector/comments');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                ],
            ],
        ]);
    }
}
