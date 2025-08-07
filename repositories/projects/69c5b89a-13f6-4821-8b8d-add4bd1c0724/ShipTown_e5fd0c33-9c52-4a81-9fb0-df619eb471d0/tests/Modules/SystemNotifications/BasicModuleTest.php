<?php

namespace Tests\Modules\SystemNotifications;
use PHPUnit\Framework\Attributes\Test;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Modules\SystemNotifications\src\SystemNotificationsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SystemNotificationsServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality(): void
    {
        $this->assertTrue(true, 'Module does not have any functionality yet');
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
