<?php

namespace App\Modules\Inventory\src;

use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Models\ManualRequestJob;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;

class InventoryServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Inventory';

    public static string $module_description = 'Provides inventory management functionality.';

    public static bool $autoEnable = true;

    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],

        EveryHourEvent::class => [
            Listeners\EveryHourEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        ManualRequestJob::query()->create([
            'job_name' => 'Recalculate Inventory',
            'job_class' => RecalculateInventoryRecordsJob::class,
        ]);

        return parent::enabling();
    }

    public static function disabling(): bool
    {
        ManualRequestJob::query()->where('job_class', RecalculateInventoryRecordsJob::class)->delete();

        return parent::disabling();
    }
}
