<?php

namespace App\Modules\AssemblyProducts\src;

use App\Modules\BaseModuleServiceProvider;
use Illuminate\Database\Events\NoPendingMigrations;

class AssemblyProductsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Assembly Products';

    public static string $module_description = 'Module provides the ability to create assembly products';

    public static bool $autoEnable = true;

    protected $listen = [
        // NoPendingMigrations::class => [
        //     Listeners\NoPendingMigrationsListener::class,
        // ],
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
