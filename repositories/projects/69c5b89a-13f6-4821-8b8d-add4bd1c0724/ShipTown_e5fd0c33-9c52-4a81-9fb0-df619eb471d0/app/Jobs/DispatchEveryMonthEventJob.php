<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryMonthEvent;

class DispatchEveryMonthEventJob extends UniqueJob
{
    public function handle(): void
    {
        EveryMonthEvent::dispatch();
    }
}
