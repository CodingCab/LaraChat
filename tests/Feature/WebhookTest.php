<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.webhook.secret' => 'test-webhook-secret']);
    }

    public function test_webhook_requires_valid_signature()
    {
        $payload = json_encode(['message' => 'Test message']);
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true));
        
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_webhook_accepts_valid_hmac_signature()
    {
        Queue::fake();
        
        $payload = json_encode([
            'message' => 'Test webhook message',
            'repository' => 'test-repo',
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'conversation_id',
                'message',
            ])
            ->assertJson(['status' => 'success']);
        
        $this->assertDatabaseHas('conversations', [
            'message' => 'Test webhook message',
            'repository' => 'test-repo',
        ]);
    }

    public function test_webhook_accepts_valid_plain_signature()
    {
        Queue::fake();
        
        $payload = json_encode([
            'message' => 'Test webhook message with plain signature',
        ]);
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => 'test-webhook-secret',
        ]);
        
        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);
        
        $this->assertDatabaseHas('conversations', [
            'message' => 'Test webhook message with plain signature',
        ]);
    }

    public function test_webhook_creates_conversation_with_user_email()
    {
        Queue::fake();
        
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $payload = json_encode([
            'message' => 'Test message with user',
            'user_email' => 'test@example.com',
            'repository' => 'user-repo',
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(201);
        
        $conversation = Conversation::where('message', 'Test message with user')->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($user->id, $conversation->user_id);
        $this->assertEquals('user-repo', $conversation->repository);
    }

    public function test_webhook_creates_default_user_if_email_not_found()
    {
        Queue::fake();
        
        $payload = json_encode([
            'message' => 'Test message without user',
            'user_email' => 'nonexistent@example.com',
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(201);
        
        $webhookUser = User::where('email', 'webhook@system.local')->first();
        $this->assertNotNull($webhookUser);
        $this->assertEquals('Webhook System', $webhookUser->name);
        
        $conversation = Conversation::where('message', 'Test message without user')->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($webhookUser->id, $conversation->user_id);
    }

    public function test_webhook_requires_message_field()
    {
        $payload = json_encode([
            'repository' => 'test-repo',
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(422)
            ->assertJson(['error' => 'Missing required field: message']);
    }

    public function test_webhook_handles_invalid_json()
    {
        $response = $this->call('POST', '/api/webhooks', [], [], [], [
            'HTTP_X-Webhook-Signature' => 'test-webhook-secret',
        ], 'invalid-json');
        
        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid JSON payload']);
    }

    public function test_webhook_handles_long_messages()
    {
        Queue::fake();
        
        $longMessage = str_repeat('This is a very long message. ', 100);
        
        $payload = json_encode([
            'message' => $longMessage,
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(201);
        
        $conversation = Conversation::latest()->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($longMessage, $conversation->message);
        // Title should be truncated to 100 chars + '...'
        $this->assertLessThanOrEqual(103, strlen($conversation->title));
    }

    public function test_webhook_works_without_repository()
    {
        Queue::fake();
        
        $payload = json_encode([
            'message' => 'Test message without repository',
        ]);
        
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');
        
        $response = $this->postJson('/api/webhooks', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        
        $response->assertStatus(201);
        
        $conversation = Conversation::where('message', 'Test message without repository')->first();
        $this->assertNotNull($conversation);
        $this->assertNull($conversation->repository);
    }
}