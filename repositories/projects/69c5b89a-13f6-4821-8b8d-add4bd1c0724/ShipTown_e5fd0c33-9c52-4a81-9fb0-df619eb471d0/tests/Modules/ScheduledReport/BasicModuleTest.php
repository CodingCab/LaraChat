<?php

namespace Tests\Modules\ScheduledReport;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Modules\ScheduledReport\src\ScheduledReportModulesServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ScheduledReportModulesServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->assertTrue(true, 'This test has not been implemented yet.');
    }

    #[Test]
    public function testIfNoErrorsDuringEvents()
    {
        EveryMinuteEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}
