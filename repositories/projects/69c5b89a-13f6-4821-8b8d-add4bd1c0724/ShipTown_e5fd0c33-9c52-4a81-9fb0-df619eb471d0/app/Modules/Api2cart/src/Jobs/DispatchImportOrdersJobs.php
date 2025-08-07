<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Api2cart\src\Models\Api2cartConnection;

class DispatchImportOrdersJobs extends UniqueJob
{
    public function handle(): void
    {
        foreach (Api2cartConnection::all() as $api2cartConnection) {
            ImportOrdersJobs::dispatch($api2cartConnection);
        }
    }
}
