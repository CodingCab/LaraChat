<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\EveryMinuteEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;

class EveryMinuteEventListener
{
    public function handle(EveryMinuteEvent $event): void
    {
        SyncMissingReservationsJob::dispatch(now()->subMinutes(2));
    }
}
