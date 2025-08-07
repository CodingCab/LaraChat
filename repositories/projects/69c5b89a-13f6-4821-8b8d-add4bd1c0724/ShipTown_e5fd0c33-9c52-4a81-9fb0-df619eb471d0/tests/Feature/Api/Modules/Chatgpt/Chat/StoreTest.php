<?php

namespace Tests\Feature\Api\Modules\Chatgpt\Chat;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/modules/chatgpt/chat';

    #[Test]
    public function testIfCallReturnsOk()
    {
        if (! env('OPENAI_API_KEY')) {
            $this->markTestSkipped('OPENAI_API_KEY is not set in .env file');
            return;
        }

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'message' => 'Hello',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['reply']]);
    }
}

