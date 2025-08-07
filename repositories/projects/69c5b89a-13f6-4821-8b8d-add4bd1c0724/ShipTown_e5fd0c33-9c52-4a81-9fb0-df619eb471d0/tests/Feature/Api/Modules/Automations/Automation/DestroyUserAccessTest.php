<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;

use PHPUnit\Framework\Attributes\Test;
use App\Modules\Automations\src\Models\Automation;
use App\User;
use Tests\TestCase;

class DestroyUserAccessTest extends TestCase
{
    #[Test]
    public function test_user_access(): void
    {
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson(route('api.modules.automations.destroy', $automation->getKey()), []);

        $response->assertForbidden();
    }
}
