<?php

namespace App\Modules\AutoStatusRefill\src\Listeners\EveryMinuteEvent;

use App\Modules\AutoStatusRefill\src\Jobs\RefillStatusesJob;

class RefillStatusesListener
{
    public function handle(): void
    {
        RefillStatusesJob::dispatch();
    }
}
