<?php

namespace Tests\Feature\Api\DataCollector\Comments;
use PHPUnit\Framework\Attributes\Test;

use App\Models\DataCollection;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    #[Test]
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $dataCollection = DataCollection::factory()->create();

        $attributes = [
            'data_collection_id' => $dataCollection->getKey(),
            'comment' => 'Test comment',
        ];

        $response = $this->actingAs($user, 'api')->postJson('api/data-collector/comments', $attributes);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'data_collection_id',
                    'user_id',
                    'comment',
                ],
            ],
        ]);
    }
}
