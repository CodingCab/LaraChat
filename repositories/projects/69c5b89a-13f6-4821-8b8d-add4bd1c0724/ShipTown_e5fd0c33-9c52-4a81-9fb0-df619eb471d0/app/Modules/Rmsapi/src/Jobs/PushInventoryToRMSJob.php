<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Rmsapi\src\Api\Client;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;

class PushInventoryToRMSJob extends UniqueJob
{
    public string $batch_uuid;

    public function __construct()
    {
        $this->batch_uuid = Uuid::uuid4()->toString();
    }

    /**
     * Execute the job.
     *
     *
     * @throws Exception|GuzzleException
     */
    public function handle(): bool
    {
        /** @var RmsapiConnection $rmsConnection */
        $rmsConnection = RmsapiConnection::first();

        Client::POST($rmsConnection, 'command', [
            'command' => $this->batch_uuid,
        ]);

        return true;
    }
}
