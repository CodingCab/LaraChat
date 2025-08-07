<?php

namespace Tests\Feature\Api\Picklist\Picks\Pick;
use PHPUnit\Framework\Attributes\Test;

use App\Models\Pick;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('user');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        $pick = Pick::factory()->create();

        $response = $this->delete(route('api.picklist.picks.destroy', $pick));

        $response->assertOk();
    }
}
