<?php

namespace Tests\Modules\Picklist;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Pick;
use App\Modules\Picklist\src\Jobs\DistributePicksJob;
use App\Modules\Picklist\src\Jobs\UnDistributeDeletedPicksJob;
use App\Modules\Picklist\src\PicklistServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PicklistServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality(): void
    {
        $pick = Pick::factory()->create();

        DistributePicksJob::dispatch($pick);

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => $pick->quantity_picked]);

        $pick->delete();
        UnDistributeDeletedPicksJob::dispatch($pick);

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => 0, 'deleted_at' => $pick->deleted_at]);

    }

    #[Test]
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}
