<?php

namespace App\Modules\NonInventoryProductTag\src;

use App\Events\EveryMinuteEvent;
use App\Modules\BaseModuleServiceProvider;
use Exception;

/**
 * Class Api2cartServiceProvider.
 */
class NonInventoryProductTagServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Non-Inventory Product Tag';

    public static string $module_description = 'restricts inventory movements for products with "non-inventory" tag';

    public static string $settings_link = '';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],
    ];

    /**
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    public static function disabling(): bool
    {
        //

        return parent::disabling();
    }

    public static function enabling(): bool
    {
        //

        return parent::enabling();
    }
}
