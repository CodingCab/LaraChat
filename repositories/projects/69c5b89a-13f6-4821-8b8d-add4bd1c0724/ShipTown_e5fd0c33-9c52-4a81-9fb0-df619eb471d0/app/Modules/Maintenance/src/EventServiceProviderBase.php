<?php

namespace App\Modules\Maintenance\src;

use App\Events\EveryDayEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class EventServiceProviderBase extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Maintenance';

    public static string $module_description = 'Daily Maintenance Jobs';

    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],

        EveryHourEvent::class => [
            Listeners\HourlyEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\DailyEvent\RunDailyMaintenanceJobsListener::class,
            Listeners\DailyEvent\CheckLicenseExpirationListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
