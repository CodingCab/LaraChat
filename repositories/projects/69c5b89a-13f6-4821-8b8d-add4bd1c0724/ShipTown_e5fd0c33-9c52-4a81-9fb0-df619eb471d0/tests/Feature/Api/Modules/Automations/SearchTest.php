<?php

namespace Tests\Feature\Api\Modules\Automations;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchTest extends TestCase
{
    private string $uri = '/api/modules/automations';

    #[Test]
    public function test_search_returns_results(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Automation::factory()->create(['name' => 'Searchable Automation']);
        Automation::factory()->create(['name' => 'Another']);

        $response = $this->actingAs($user, 'api')->getJson($this->uri . '?filter[search_contains]=Searchable');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }
}

