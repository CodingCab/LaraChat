<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Illuminate\Support\Facades\Log;

class ImportAllJob extends UniqueJob
{
    public function handle(): bool
    {
        foreach (RmsapiConnection::all() as $rmsapiConnection) {
            ImportSalesJob::dispatchSync($rmsapiConnection->id);
            ImportShippingsJob::dispatchSync($rmsapiConnection->id);
            ImportProductsJob::dispatchSync($rmsapiConnection->id);

            Log::debug('RMSAPI Sync jobs dispatched', [
                'warehouse_code' => $rmsapiConnection->location_id,
                'connection_id' => $rmsapiConnection->id,
            ]);
        }

        return true;
    }
}
