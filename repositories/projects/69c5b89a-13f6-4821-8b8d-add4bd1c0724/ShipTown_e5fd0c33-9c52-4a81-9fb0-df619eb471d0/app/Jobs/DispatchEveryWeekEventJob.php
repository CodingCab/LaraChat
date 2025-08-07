<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryWeekEvent;

class DispatchEveryWeekEventJob extends UniqueJob
{
    public function handle(): void
    {
        EveryWeekEvent::dispatch();
    }
}
