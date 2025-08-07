<?php

namespace Tests\Feature\Api\Modules\ScheduledReports\ScheduledReport;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = '/api/modules/scheduled-reports/';

    #[Test]
    public function testIfCallReturnsOk()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $scheduledReport = ScheduledReport::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri . $scheduledReport->id);

        $response->assertSuccessful();

        $this->assertModelMissing($scheduledReport);
    }
}
