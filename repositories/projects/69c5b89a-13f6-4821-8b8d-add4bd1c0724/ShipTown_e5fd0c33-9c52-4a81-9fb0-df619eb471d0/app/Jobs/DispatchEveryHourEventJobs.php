<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryHourEvent;

class DispatchEveryHourEventJobs extends UniqueJob
{
    public function handle(): void
    {
        EveryHourEvent::dispatch();
    }
}
