<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobProcessingListener
{
    public function handle(JobProcessing $event)
    {
        $key = implode('-', ['job_start_time', $event->job->uuid()]);

        $jobName = data_get($event->job->payload(), 'displayName');

        Cache::set($key, now(), 3600);

        if (in_array($jobName, QueueMonitorServiceProvider::$ignoredJobList)) {
            return;
        }

        Log::debug('Job processing', ['job' => $jobName]);
    }
}
