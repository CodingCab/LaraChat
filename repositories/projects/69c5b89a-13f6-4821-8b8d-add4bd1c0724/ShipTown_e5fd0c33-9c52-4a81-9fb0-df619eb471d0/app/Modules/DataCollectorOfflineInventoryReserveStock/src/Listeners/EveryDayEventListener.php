<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Listeners;

use App\Events\EveryDayEvent;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;

class EveryDayEventListener
{
    public function handle(EveryDayEvent $event): void
    {
        CheckReservationsDataIntegrityJob::dispatch();
    }
}
