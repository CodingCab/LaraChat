<?php

namespace App\Modules\AutoPilot\src;

use App\Events\EveryHourEvent;
use App\Modules\AutoPilot\src\Listeners\ClearPackerIdListener;
use App\Modules\BaseModuleServiceProvider;

class AutoPilotServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Automation - Packer Collision';

    public static string $module_description = 'Locks orders from being assigned to two packers at the same time';

    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryHourEvent::class => [
            ClearPackerIdListener::class,
        ],
    ];
}
