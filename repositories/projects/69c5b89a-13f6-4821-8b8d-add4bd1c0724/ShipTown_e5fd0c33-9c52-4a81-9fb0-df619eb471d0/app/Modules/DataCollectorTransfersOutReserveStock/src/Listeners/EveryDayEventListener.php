<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\EveryDayEvent;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;

class EveryDayEventListener
{
    public function handle(EveryDayEvent $event): void
    {
        CheckReservationsDataIntegrityJob::dispatch();
    }
}
