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
    
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $user = User::factory()->create();
        
        $message = 'Hi 24';
        $response = $this->actingAs($user)
            ->get('/claude/new?repository=ShipTown&message=' . Str::toBase64($message));

        $conversation = Conversation::query()->latest()->first();

        $this->assertNotNull($conversation, 'Conversation should be created');
        $this->assertEquals($message, $conversation->message);
        $this->assertEquals('ShipTown', $conversation->repository);

        $response->assertStatus(302);
    }
}
