<?php

namespace Tests\Feature\Api\Modules\ScheduledReports\ScheduledReport;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = '/api/modules/scheduled-reports/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $scheduledReport = ScheduledReport::factory()->create();

        $updatedName = 'Updated Name';
        $updatedEmail = 'updatedemail@example.com';
        $updatedCron = '0 1 * * 1';

        $response = $this->actingAs($user, 'api')->putJson($this->uri . $scheduledReport->id, [
            'name' => $updatedName,
            'uri' => '/reports/inventory-movements?filter%5Bwarehouse_code%5D=DUB&sort=-occurred_at,-sequence_number&filter%5Boccurred_at_between%5D=7%20days%20ago,now',
            'email' => $updatedEmail,
            'cron' => $updatedCron,
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'uri',
                'email',
                'cron',
                'created_at',
                'updated_at',
            ],
        ]);

        $scheduledReport->refresh();

        $this->assertEquals($updatedName, $scheduledReport->name);
        $this->assertEquals($updatedEmail, $scheduledReport->email);
        $this->assertEquals($updatedCron, $scheduledReport->cron);
    }
}
