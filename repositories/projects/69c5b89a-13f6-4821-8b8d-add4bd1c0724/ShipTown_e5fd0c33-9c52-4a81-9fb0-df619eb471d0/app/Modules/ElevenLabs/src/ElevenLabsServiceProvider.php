<?php

namespace App\Modules\ElevenLabs\src;

use App\Modules\BaseModuleServiceProvider;

class ElevenLabsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Eleven Labs';

    public static string $module_description = 'Provides Eleven Labs integration';

    public static string $settings_link = '/settings/modules/eleven-labs';

    public static bool $autoEnable = false;

    protected $listen = [];

    public static function enabling(): bool
    {
        return true;
    }

    public static function disabling(): bool
    {
        return true;
    }
}
