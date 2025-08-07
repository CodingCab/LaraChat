<?php

namespace App\Modules\ScheduledReport\src;

use App\Events\EveryMinuteEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\ScheduledReport\src\Listeners\EveryMinuteEventListener;
use Exception;

/**
 * Class Api2cartServiceProvider.
 */
class ScheduledReportModulesServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Scheduled Report';

    public static string $module_description = 'Automates report generation and delivery on a scheduled basis.';

    public static string $settings_link = '';

    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            EveryMinuteEventListener::class
        ],
    ];
}
