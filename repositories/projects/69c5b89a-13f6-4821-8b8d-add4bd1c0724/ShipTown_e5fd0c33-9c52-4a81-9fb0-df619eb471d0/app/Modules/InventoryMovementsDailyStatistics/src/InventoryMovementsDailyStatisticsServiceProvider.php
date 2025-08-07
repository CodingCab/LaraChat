<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src;

use App\Events\EveryMinuteEvent;
use App\Models\ManualRequestJob;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\CreateDaysRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\InsertDailyStatisticsRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateLast6MonthsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateStatisticsJob;

class InventoryMovementsDailyStatisticsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Inventory Movements Daily Statistics';

    public static string $module_description = 'Inventory Movements Daily Statistics';

    public static bool $autoEnable = false;

    protected array $listeners = [
        EveryMinuteEvent::class => [
            \App\Modules\InventoryMovementsDailyStatistics\src\Listeners\EveryMinuteEventListener::class,
        ],
        \App\Events\EveryDayEvent::class => [
            \App\Modules\InventoryMovementsDailyStatistics\src\Listeners\EveryDayEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        ManualRequestJob::query()->updateOrCreate([
            'job_class' => CreateDaysRecordsJob::class,
        ], [
            'job_name' => 'Recalculate Inventory Movements Daily Statistics Records (last 6 months)',
        ]);

        ManualRequestJob::query()->updateOrCreate([
            'job_class' => RecalculateRecordsJob::class,
        ], [
            'job_name' => 'Recalculate Inventory Movements Daily Statistics Records',
        ]);

        ManualRequestJob::query()->updateOrCreate([
            'job_class' => InsertDailyStatisticsRecordsJob::class,
        ], [
            'job_name' => 'Insert Inventory Movements Daily Statistics Records',
        ]);

        RecalculateStatisticsJob::dispatch();

        return true;
    }

    public static function disabling(): bool
    {
        ManualRequestJob::query()->where('job_class', RecalculateRecordsJob::class)->delete();
        ManualRequestJob::query()->where('job_class', RecalculateLast6MonthsJob::class)->delete();

        return true;
    }
}
