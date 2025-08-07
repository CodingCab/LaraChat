<?php

namespace Tests\Feature\Api\Product\Aliases;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.product.aliases.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                ],
            ],
        ]);
    }
}
