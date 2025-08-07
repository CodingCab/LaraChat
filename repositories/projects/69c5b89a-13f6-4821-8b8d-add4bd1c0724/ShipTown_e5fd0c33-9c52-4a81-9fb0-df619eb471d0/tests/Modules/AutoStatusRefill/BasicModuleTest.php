<?php

namespace Tests\Modules\AutoStatusRefill;

use App\Jobs\DispatchEveryMinuteEventJob;
use App\Modules\AutoStatusRefill\src\AutoStatusRefillServiceProvider;
use App\Modules\AutoStatusRefill\src\Jobs\RefillStatusesJob;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_basic_functionality(): void
    {
        AutoStatusRefillServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Bus::fake();

        $job = new DispatchEveryMinuteEventJob();
        $job->handle();

        Bus::assertDispatched(RefillStatusesJob::class);
    }
}
