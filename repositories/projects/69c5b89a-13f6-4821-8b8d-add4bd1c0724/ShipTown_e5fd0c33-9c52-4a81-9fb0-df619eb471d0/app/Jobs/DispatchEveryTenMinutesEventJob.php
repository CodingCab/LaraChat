<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryTenMinutesEvent;

class DispatchEveryTenMinutesEventJob extends UniqueJob
{
    public function handle(): void
    {
        EveryTenMinutesEvent::dispatch();
    }
}
