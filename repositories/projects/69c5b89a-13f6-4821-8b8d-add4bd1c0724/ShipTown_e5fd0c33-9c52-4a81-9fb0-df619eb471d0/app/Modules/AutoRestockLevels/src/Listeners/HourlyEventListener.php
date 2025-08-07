<?php

namespace App\Modules\AutoRestockLevels\src\Listeners;

use App\Modules\AutoRestockLevels\src\Jobs\SetMissingRestockLevels;

class HourlyEventListener
{
    public function handle()
    {
        SetMissingRestockLevels::dispatch();
    }
}
