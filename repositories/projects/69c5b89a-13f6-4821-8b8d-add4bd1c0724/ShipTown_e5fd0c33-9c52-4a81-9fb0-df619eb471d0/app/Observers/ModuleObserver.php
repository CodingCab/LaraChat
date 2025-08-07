<?php

namespace App\Observers;

use App\Models\Module;
use App\Modules\BaseModuleServiceProvider;

class ModuleObserver
{
    public function saving(Module $module): bool
    {
        if ($module->isAttributeChanged('enabled')) {
            /** @var BaseModuleServiceProvider $service */
            $service = $module->service_provider_class;

            return $module->enabled ? $service::enabling() : $service::disabling();
        }

        return true;
    }
}
