<?php

namespace App\Modules\OrderTotals\src;

use App\Events\EveryDayEvent;
use App\Events\EveryMinuteEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class ServiceProvider
 */
class OrderTotalsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Order Totals';

    public static string $module_description = 'Updates order totals like total_quantity_to_ship';

    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],

        OrderCreatedEvent::class => [
            Listeners\OrderCreatedEventListener::class,
        ],

        OrderProductCreatedEvent::class => [
            Listeners\OrderProductCreatedEventListener::class,
        ],

        OrderProductUpdatedEvent::class => [
            Listeners\OrderProductUpdatedEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        return true;
    }

    public static function disabling(): bool
    {
        return false;
    }
}
