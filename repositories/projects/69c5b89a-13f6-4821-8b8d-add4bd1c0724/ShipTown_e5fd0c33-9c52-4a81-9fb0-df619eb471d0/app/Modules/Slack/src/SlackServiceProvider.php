<?php

namespace App\Modules\Slack\src;

use App\Modules\BaseModuleServiceProvider;
use Exception;

/**
 * Class Api2cartServiceProvider.
 */
class SlackServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Slack Integration';

    public static string $module_description = 'Provides Slack integration';

    public static string $settings_link = '/modules/slack/config';

    public static bool $autoEnable = false;

    protected $listen = [];

    /**
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        $module_filename = 'Slack';

        $this->loadViewsFrom(__DIR__.'/../resources/views', $module_filename);

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
