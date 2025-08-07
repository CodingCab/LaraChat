<?php

namespace App\Modules\AutoPilot\src\Listeners;

use App\Modules\AutoPilot\src\Jobs\ClearPackerIdJob;

class ClearPackerIdListener
{
    public function handle()
    {
        ClearPackerIdJob::dispatch();
    }
}
