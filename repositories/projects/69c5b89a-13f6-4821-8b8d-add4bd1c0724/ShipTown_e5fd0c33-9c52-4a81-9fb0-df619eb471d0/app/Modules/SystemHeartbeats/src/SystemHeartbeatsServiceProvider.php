<?php

namespace App\Modules\SystemHeartbeats\src;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryMonthEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\EveryWeekEvent;
use App\Modules\BaseModuleServiceProvider;

class SystemHeartbeatsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'System Heartbeats';

    public static string $module_description = 'This module monitors core system functionalities like events and jobs';

    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],

        EveryFiveMinutesEvent::class => [
            Listeners\EveryFiveMinutesEventListener::class,
        ],

        EveryTenMinutesEvent::class => [
            Listeners\EveryTenMinutesEventListener::class,
        ],

        EveryHourEvent::class => [
            Listeners\EveryHourEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],

        EveryWeekEvent::class => [
            Listeners\EveryWeekEventListener::class,
        ],

        EveryMonthEvent::class => [
            Listeners\EveryMonthEventListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
