<?php

namespace App\Modules\_SampleModuleStructure\src\Listeners;

use App\Events\EveryDayEvent;

class DailyEventListener
{
    public function handle(EveryDayEvent $event): bool
    {
        //

        return true;
    }
}
