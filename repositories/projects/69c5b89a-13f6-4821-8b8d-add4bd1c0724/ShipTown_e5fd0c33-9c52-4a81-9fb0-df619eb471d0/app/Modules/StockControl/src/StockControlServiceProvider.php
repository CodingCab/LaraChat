<?php

namespace App\Modules\StockControl\src;

use App\Events\OrderProduct\OrderProductShipmentCreatedEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class ServiceProvider
 */
class StockControlServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Stock Control';

    public static string $module_description = 'Increase \ Decrease inventory when product shipped';

    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderProductShipmentCreatedEvent::class => [
            Listeners\OrderProductShipmentCreatedListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
