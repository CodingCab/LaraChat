<?php

namespace App\Modules;

use App\Models\Module;
use Exception;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class BaseModuleServiceProvider.
 */
abstract class BaseModuleServiceProvider extends EventServiceProvider
{
    public static string $module_name = '';

    public static string $module_description = '';

    public static string $settings_link = '';

    /**
     * Should we automatically enable it
     * When module first registered.
     */
    public static bool $autoEnable = false;

    /**
     * Register any events for your application.
     *
     * @return void
     *
     * @throws Exception
     */
    public function boot()
    {
        if (empty(static::$module_name) or empty(static::$module_description)) {
            throw new Exception('Module "'.get_called_class().'" missing name or description');
        }

        parent::boot();
    }

    public static function loaded(): bool
    {
        // this method is fired when module booted and enabled
        // return false if you want to prevent loading
        return true;
    }

    public static function installing(): bool
    {
        // this method is fired when module is being installed
        // return false if you want to prevent installing
        return true;
    }

    public static function enabling(): bool
    {
        // this method is fired when module is being enabled
        // return false if you want to prevent enabling
        return true;
    }

    public static function disabling(): bool
    {
        // this method is fired when module is being disabled
        // return false if you want to prevent disabling
        return true;
    }

    public static function isEnabled(): bool
    {
        try {
            $module = Module::firstOrCreate([
                'service_provider_class' => get_called_class(),
            ], [
                'enabled' => self::$autoEnable,
                'name' => self::$module_name,
                'description' => self::$module_description,
                'settings_link' => self::$settings_link,
            ]);

            return $module->enabled;
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
    }

    public static function isDisabled(): bool
    {
        return ! self::isEnabled();
    }

    public static function enableModule(): bool
    {
        $module = Module::firstOrCreate([
            'service_provider_class' => get_called_class()
        ], [
            'enabled' => false,
            'name' => self::$module_name,
            'description' => self::$module_description,
            'settings_link' => self::$settings_link,
        ]);

        if ($module->enabled) {
            return true;
        }

        $module->enabled = true;

        if (! $module->save()) {
            return false;
        }

        app()->singleton(get_called_class(), get_called_class());

        App::register(get_called_class());

        get_called_class()::loaded();

        return true;
    }

    public static function uninstallModule(): bool
    {
        try {
            /** @var Module $module */
            $module = Module::query()->where(['service_provider_class' => get_called_class()])->first();

            $module?->forceDelete();

            return true;
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
    }

    public static function installModule(): bool
    {
        try {
            /** @var BaseModuleServiceProvider $moduleServiceProvider */
            $moduleServiceProvider = get_called_class();

            if (! $moduleServiceProvider::installing()) {
                return false;
            }

            Module::query()->firstOrCreate([
                'service_provider_class' => $moduleServiceProvider,
            ], [
                'enabled' => $moduleServiceProvider::$autoEnable,
                'name' => $moduleServiceProvider::$module_name,
                'description' => $moduleServiceProvider::$module_description,
                'settings_link' => $moduleServiceProvider::$settings_link,
            ]);

            return true;
        } catch (Exception $exception) {
            Log::emergency($exception);

            return false;
        }
    }

    public static function disableModule(): void
    {
        $module = Module::query()->firstOrCreate([
            'service_provider_class' => get_called_class(),
        ], [
            'enabled' => false,
            'name' => self::$module_name,
            'description' => self::$module_description,
            'settings_link' => self::$settings_link,
        ]);

        if ($module->enabled) {
            $module->update(['enabled' => false]);
        }
    }
}
