<?php

namespace App\Modules\ScurriAnpost\src;

use App\Models\ShippingService;
use App\Modules\BaseModuleServiceProvider;

class ScurriServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - An Post Ireland';

    public static string $module_description = 'Provides seamless integration with AnPost Ireland';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    public static function enabling(): bool
    {
        ShippingService::query()
            ->updateOrCreate([
                'code' => 'anpost_3day',
            ], [
                'service_provider_class' => Services\AnPostShippingService::class,
            ]);

        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()
            ->where(['code' => 'anpost_3day'])
            ->delete();

        return true;
    }

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/scurri.php' => config_path('scurri.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/scurri.php', 'scurri');
    }
}
