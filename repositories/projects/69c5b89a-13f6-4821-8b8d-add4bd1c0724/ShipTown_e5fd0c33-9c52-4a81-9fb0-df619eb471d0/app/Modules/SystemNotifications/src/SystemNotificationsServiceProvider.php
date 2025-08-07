<?php

namespace App\Modules\SystemNotifications\src;

use App\Events\EveryDayEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class Api2cartServiceProvider.
 */
class SystemNotificationsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - System Notifications';

    public static string $module_description = 'Sends notifications to admins when certain events occur';

    public static string $settings_link = '';

    public static bool $autoEnable = true;

    protected $listen = [
        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
