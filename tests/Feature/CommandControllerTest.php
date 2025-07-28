<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_run_pwd_command()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/run-command', [
                'command' => 'pwd'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'output',
                'success'
            ])
            ->assertJson([
                'success' => true
            ]);

        $this->assertNotEmpty($response->json('output'));
    }

    public function test_unauthenticated_user_cannot_run_command()
    {
        $response = $this->postJson('/api/run-command', [
            'command' => 'pwd'
        ]);

        $response->assertStatus(401);
    }

    public function test_only_pwd_command_is_allowed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/run-command', [
                'command' => 'ls'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['command']);
    }

    public function test_command_field_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/run-command', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['command']);
    }

    public function test_command_must_be_string()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/run-command', [
                'command' => 123
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['command']);
    }
}