<?php

namespace App\Modules\Automations\src;

use App\Events\EveryHourEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderShipment\OrderShipmentCreatedEvent;
use App\Events\ShippingLabel\ShippingLabelCreatedEvent;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\BaseModuleServiceProvider;

class AutomationsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Automations - Active Orders';

    public static string $module_description = 'Sporadically, when required, runs automations on active orders';

    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryTenMinutesEvent::class => [
            Listeners\EveryTenMinutesEventListener::class,
        ],

        EveryHourEvent::class => [
            Listeners\HourlyEventListener::class,
        ],

        OrderCreatedEvent::class => [
            Listeners\OrderCreatedListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedListener::class,
        ],

        ShippingLabelCreatedEvent::class => [
            Listeners\ShippingLabelCreatedListener::class,
        ],

        OrderShipmentCreatedEvent::class => [
            Listeners\OrderShipmentCreatedEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        RunEnabledAutomationsJob::dispatch();

        return parent::enabling();
    }
}
