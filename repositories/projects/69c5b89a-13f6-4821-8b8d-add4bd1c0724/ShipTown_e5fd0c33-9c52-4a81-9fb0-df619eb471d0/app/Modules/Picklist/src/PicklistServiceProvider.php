<?php

namespace App\Modules\Picklist\src;

use App\Events\EveryMinuteEvent;
use App\Events\Pick\PickDeletedEvent;
use App\Modules\BaseModuleServiceProvider;

class PicklistServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Picklist';

    public static string $module_description = 'Picklist module';

    public static bool $autoEnable = true;

    protected $listen = [
        PickDeletedEvent::class => [
            Listeners\PickDeletedListener::class,
        ],

        EveryMinuteEvent::class => [
            Listeners\EveryMinuteListener::class,
        ],
    ];
}
