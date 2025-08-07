<?php

namespace App\Modules\AutoStatusRefill\src;

use App\Events\EveryMinuteEvent;
use App\Models\ManualRequestJob;
use App\Modules\AutoStatusRefill\src\Jobs\RefillStatusesJob;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class AutoStatusRefillServiceProvider.
 */
class AutoStatusRefillServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Order Automation - Auto Status Refilling';

    public static string $module_description = 'Set desired order count with status name and automatically refill when needed';

    public static string $settings_link = '/settings/modules/auto-picking-refilling';

    public static bool $autoEnable = false;

    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEvent\RefillStatusesListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        RefillStatusesJob::dispatch();

        ManualRequestJob::query()->updateOrCreate([
            'job_class' => RefillStatusesJob::class,
        ], [
            'job_name' => 'Order Automations - Refill Statuses',
        ]);

        return true;
    }

    public static function disabling(): bool
    {
        ManualRequestJob::query()->where('job_class', RefillStatusesJob::class)->delete();

        return true;
    }
}
