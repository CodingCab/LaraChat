<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations\Listeners;

use App\Events\EveryMinuteEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Jobs\SyncMissingReservationsJob;
use App\Modules\ActiveOrdersInventoryReservations\src\Listeners\EveryMinuteEventListener;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EveryMinuteEventListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    public function test_listener_dispatches_sync_job_with_two_minutes_ago()
    {
        Bus::fake();

        $beforeDispatch = now();
        
        $listener = new EveryMinuteEventListener();
        $listener->handle(new EveryMinuteEvent());

        Bus::assertDispatched(function (SyncMissingReservationsJob $job) use ($beforeDispatch) {
            // Check that the job was dispatched with a datetime around 2 minutes ago
            $expectedTime = $beforeDispatch->subMinutes(2);
            $actualTime = $job->createdAfter;
            
            // Allow 1 second tolerance for test execution time
            return $actualTime instanceof Carbon 
                && abs($expectedTime->diffInSeconds($actualTime)) <= 1;
        });
    }
}