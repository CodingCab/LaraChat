<?php

namespace Tests\Feature\Api\Modules\Printnode\Clients\Client;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\PrintNode\src\Models\Client;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    #[Test]
    public function test_destroy_call_returns_ok(): void
    {
        $client = Client::factory()->create();

        $response = $this->delete(route('api.modules.printnode.clients.destroy', $client));

        $response->assertSuccessful();
    }
}
