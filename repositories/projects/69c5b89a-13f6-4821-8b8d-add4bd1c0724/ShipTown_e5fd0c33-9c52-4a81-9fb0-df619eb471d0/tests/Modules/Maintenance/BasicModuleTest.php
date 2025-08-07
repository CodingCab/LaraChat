<?php

namespace Tests\Modules\Maintenance;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Modules\Maintenance\src\EventServiceProviderBase;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllInventoryRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllProductPriceRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\Products\FixQuantityAvailableJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    #[Test]
    public function test_module_basic_functionality(): void
    {
        EventServiceProviderBase::enableModule();

        Bus::fake();

        EveryDayEvent::dispatch();

        Bus::assertDispatched(EnsureAllInventoryRecordsExistsJob::class);
        Bus::assertDispatched(EnsureAllProductPriceRecordsExistsJob::class);
        Bus::assertDispatched(FixQuantityAvailableJob::class);
    }
}
