<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Modules\InventoryMovementsDailyStatistics\src\InventoryMovementsDailyStatisticsServiceProvider;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateStatisticsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDay;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsDailyStatisticsServiceProvider::enableModule();
    }

    public function testCalculatesRecord()
    {
        InventoryMovement::factory()->create();

        SequenceNumberJob::dispatchSync();

        RecalculateStatisticsJob::dispatchSync();

        ray([
            'days' => InventoryMovementsDay::query()->get()->toArray(),
            'stats' => InventoryMovementsDailyStatistic::query()->get()->toArray()
        ])->expandAll();

        $this->assertDatabaseHas('inventory_movements_daily_statistics', [
            'recalc_required' => false,
        ]);
    }


    #[Test]
    public function testIfNoErrorsDuringEvents()
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}
