<?php

namespace Tests\Feature\Api\Modules\ScheduledReports;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = '/api/modules/scheduled-reports';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        ScheduledReport::factory()->create();

        $response = $this->actingAs($user, 'api')->get($this->uri);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'uri',
                    'email',
                    'cron',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    #[Test]
    public function testUserAccess()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->get($this->uri, []);

        $response->assertSuccessful();
    }
}
