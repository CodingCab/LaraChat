<?php

namespace App\Modules\Rmsapi\src\Listeners;

use App\Modules\Rmsapi\src\Jobs\ImportAllJob;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedProductRecordsJob;

class EveryFiveMinutesEventListener
{
    public function handle()
    {
        ImportAllJob::dispatch();
        ProcessImportedProductRecordsJob::dispatch();
    }
}
