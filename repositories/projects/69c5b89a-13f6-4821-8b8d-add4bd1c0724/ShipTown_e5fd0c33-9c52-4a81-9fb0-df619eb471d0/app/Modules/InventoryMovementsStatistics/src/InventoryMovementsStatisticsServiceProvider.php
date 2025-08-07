<?php

namespace App\Modules\InventoryMovementsStatistics\src;

use App\Events\EveryDayEvent;
use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use App\Modules\BaseModuleServiceProvider;

class InventoryMovementsStatisticsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Inventory Movements Statistics';

    public static string $module_description = 'Statistics like "Quantity sold in last 7 days"';

    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        InventoryMovementCreatedEvent::class => [
            Listeners\InventoryMovementCreatedEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],

        RecalculateInventoryRequestEvent::class => [
            Listeners\RecalculateInventoryRequestEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }

    public static function disabling(): bool
    {
        return false;
    }
}
