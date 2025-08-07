<?php

namespace App\Modules\Permissions\src;

use App\Modules\BaseModuleServiceProvider;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\NoPendingMigrations;

class PermissionsModuleServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Permissions';

    public static string $module_description = 'Manages user roles and permissions.';

    public static string $settings_link = '/settings/modules/permissions';

    public static bool $autoEnable = true;

    protected $listen = [
        MigrationsEnded::class => [
            Listeners\MigrationsEndedListener::class,
        ],
        NoPendingMigrations::class => [
            Listeners\NoPendingMigrationsListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
