<?php

namespace App\Modules\_SampleModuleStructure\src;

use App\Modules\BaseModuleServiceProvider;
use Exception;

/**
 * Class Api2cartServiceProvider.
 */
class SampleModulesServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '';

    public static string $module_description = '';

    public static string $settings_link = '';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //        Every10minEvent::class => [],
        //
        //        HourlyEvent::class => [],
        //
        //        DailyEvent::class => [],
        //
        //        ProductPriceUpdatedEvent::class => [],
        //
        //        ProductTagAttachedEvent::class => [],
        //
        //        ProductTagDetachedEvent::class => [],
        //
        //        InventoryUpdatedEvent::class => [],
        //
        //        OrderUpdatedEvent::class => []
    ];

    /**
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        $module_filename = '';

        $this->publishes(
            [__DIR__.'/../config/'.$module_filename.'.php' => config_path('_SampleModuleStructure.php')],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/'.$module_filename.'.php',
            $module_filename
        );

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            $module_filename
        );

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    public static function disabling(): bool
    {
        return parent::disabling();
    }

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
