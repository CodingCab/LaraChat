<?php

namespace App\Modules\OrderTotals\src\Listeners;

use App\Modules\OrderTotals\src\Jobs\EnsureAllRecordsExistsJob;
use App\Modules\OrderTotals\src\Jobs\EnsureCorrectTotalsJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        EnsureAllRecordsExistsJob::dispatch(now()->subHour(), now());
        EnsureCorrectTotalsJob::dispatch(now()->subHour(), now());
    }
}
