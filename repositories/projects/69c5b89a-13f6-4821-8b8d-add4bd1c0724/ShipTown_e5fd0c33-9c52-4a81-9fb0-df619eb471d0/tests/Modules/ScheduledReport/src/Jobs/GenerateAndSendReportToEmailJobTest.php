<?php

namespace Tests\Modules\ScheduledReport\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ScheduledReport\src\Jobs\GenerateAndSendReportToEmailJob;
use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use Carbon\Carbon;

class GenerateAndSendReportToEmailJobTest extends JobTestAbstract
{
    public function test_job()
    {
        InventoryMovement::factory()->create();

        $scheduledReport = ScheduledReport::factory()->create();

        $cron = new \Cron\CronExpression($scheduledReport->cron);
        $nextCron = $cron->getNextRunDate();

        GenerateAndSendReportToEmailJob::dispatchSync($scheduledReport);

        $expectedNextRunAt = Carbon::parse($nextCron);
        $scheduledReport->refresh();
        $this->assertTrue($expectedNextRunAt == $scheduledReport->next_run_at, $scheduledReport);

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
