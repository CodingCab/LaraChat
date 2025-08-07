<?php

namespace App\Modules\ScheduledReport\src\Listeners;

use App\Modules\ScheduledReport\src\Jobs\GenerateAndSendReportToEmailJob;
use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use Illuminate\Support\Facades\Log;

class EveryMinuteEventListener
{
    public function handle()
    {
        Log::debug('Scheduled report', ['event' => self::class]);

        ScheduledReport::where('next_run_at', '<=', now())
            ->get()
            ->each(function (ScheduledReport $scheduledReport) {
                GenerateAndSendReportToEmailJob::dispatch($scheduledReport);
            });
    }
}
