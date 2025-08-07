<?php

namespace App\Modules\OrderStatus\src\Listeners;

use App\Modules\OrderStatus\src\Jobs\EnsureCorrectIsActiveAndIsOnHoldJob;

class HourlyEventListener
{
    public function handle()
    {
        EnsureCorrectIsActiveAndIsOnHoldJob::dispatch();
    }
}
