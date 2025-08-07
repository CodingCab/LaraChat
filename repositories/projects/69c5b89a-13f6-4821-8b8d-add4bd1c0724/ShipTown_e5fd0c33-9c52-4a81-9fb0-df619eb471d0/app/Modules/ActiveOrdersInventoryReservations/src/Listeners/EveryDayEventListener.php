<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\EveryDayEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;

class EveryDayEventListener
{
    public function handle(EveryDayEvent $event): void
    {
        // Run sync for all active orders without date filter
        SyncMissingReservationsJob::dispatch();
    }
}
