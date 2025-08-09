<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConversationsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $user = User::factory()->create();

        $number = rand(1, 1000);

        $message = 'Hi! Multiply me by 2 this number: ' . $number;
        $response = $this->actingAs($user)
            ->get('/claude/new?repository=ShipTown&message=' . Str::toBase64($message));

        $conversation = Conversation::query()->latest()->first();

        $this->assertNotNull($conversation, 'Conversation should be created');
        $this->assertEquals($message, $conversation->message);
        $this->assertEquals('ShipTown', $conversation->repository);

        $response->assertStatus(302);
    }
}
