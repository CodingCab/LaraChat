<?php

namespace Tests\Feature;

use App\Models\Heartbeat;
use App\Modules\SystemHeartbeats\src\Listeners\EveryMinuteEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryFiveMinutesEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryTenMinutesEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryHourEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryDayEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryWeekEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryMonthEventListener;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InsertDefaultSystemHeartbeatsMigrationTest extends TestCase
{
    #[Test]
    public function migration_creates_default_records(): void
    {
        $expected = [
            EveryMinuteEventListener::class,
            EveryFiveMinutesEventListener::class,
            EveryTenMinutesEventListener::class,
            EveryHourEventListener::class,
            EveryDayEventListener::class,
            EveryWeekEventListener::class,
            EveryMonthEventListener::class,
        ];

        $this->assertSame(0, Heartbeat::count());

        (require database_path('migrations/2025_07_12_000000_insert_default_system_heartbeats.php'))->up();

        $this->assertSame(7, Heartbeat::count());

        foreach ($expected as $code) {
            $this->assertDatabaseHas('heartbeats', ['code' => $code]);
        }
    }
}
