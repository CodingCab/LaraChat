<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryFiveMinutesEvent;

class DispatchEveryFiveMinutesEventJob extends UniqueJob
{
    public function handle(): void
    {
        EveryFiveMinutesEvent::dispatch();
    }
}
