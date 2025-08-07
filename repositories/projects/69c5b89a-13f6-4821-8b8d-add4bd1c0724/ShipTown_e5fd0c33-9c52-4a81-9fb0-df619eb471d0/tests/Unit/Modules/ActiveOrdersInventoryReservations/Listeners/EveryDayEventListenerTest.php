<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations\Listeners;

use App\Events\EveryDayEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EveryDayEventListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    public function testDispatchesSyncMissingReservationsJobOnDailyEvent(): void
    {
        Bus::fake();

        EveryDayEvent::dispatch();

        Bus::assertDispatched(SyncMissingReservationsJob::class, function ($job) {
            // Check that the job is dispatched without the createdAfter parameter
            // (runs for all active orders)
            return $job->createdAfter === null;
        });
    }
}