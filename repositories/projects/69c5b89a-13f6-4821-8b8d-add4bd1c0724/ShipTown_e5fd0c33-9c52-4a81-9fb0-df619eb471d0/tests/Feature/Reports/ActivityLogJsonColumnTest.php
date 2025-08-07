<?php

namespace Tests\Feature\Reports;

use App\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogJsonColumnTest extends TestCase
{
    #[Test]
    public function properties_column_is_json_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/reports/activity-log?filename=data.json');

        $response->assertOk();

        $column = collect($response->json('meta.columns'))
            ->firstWhere('name', 'properties');

        $this->assertSame('json', $column['type']);
    }
}
