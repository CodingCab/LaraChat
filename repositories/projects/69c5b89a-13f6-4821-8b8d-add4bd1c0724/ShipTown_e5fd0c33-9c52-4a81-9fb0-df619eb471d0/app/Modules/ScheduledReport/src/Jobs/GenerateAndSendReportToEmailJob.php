<?php

namespace App\Modules\ScheduledReport\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Mail\ScheduledReportMail;
use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use Illuminate\Support\Facades\Mail;

class GenerateAndSendReportToEmailJob extends UniqueJob
{
    public ScheduledReport $scheduledReport;

    public function __construct(ScheduledReport $scheduledReport)
    {
        $this->scheduledReport = $scheduledReport;
    }

    public function handle()
    {
        Mail::to($this->scheduledReport->email)->send(new ScheduledReportMail($this->scheduledReport));

        $cron = new \Cron\CronExpression($this->scheduledReport->cron);
        $nextCron = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        $this->scheduledReport->next_run_at = $nextCron;
        $this->scheduledReport->save();
    }
}
