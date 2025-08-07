<?php

namespace Tests\Feature\Api\Warehouses;
use PHPUnit\Framework\Attributes\Test;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_index_call_returns_ok(): void
    {
        $response = $this->get(route('api.warehouses.index', ['include' => 'tags']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'tags'],
            ],
        ]);
    }
}
