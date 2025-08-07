<?php

namespace App\Modules\AutoRestockLevels\src;

use App\Events\EveryHourEvent;
use App\Modules\BaseModuleServiceProvider;

class AutoRestockLevelsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Auto Restock Levels';

    public static string $module_description = 'Automatically manages stock levels based on inventory and sales';

    public static bool $autoEnable = false;

    /**
     * @var array
     */
    protected $listen = [
        EveryHourEvent::class => [
            Listeners\HourlyEventListener::class,
        ],
    ];
}
