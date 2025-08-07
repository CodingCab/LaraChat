<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryDayEvent;

class DispatchEveryDayEventJob extends UniqueJob
{
    public function handle(): void
    {
        EveryDayEvent::dispatch();
    }
}
