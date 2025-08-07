<?php

namespace App\Modules\BoxTop\src;

use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class BoxTopServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'BoxTop API Integration';

    public static string $module_description = 'Seamless integration with https://api.boxtrax.com/Help';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    ];
}
