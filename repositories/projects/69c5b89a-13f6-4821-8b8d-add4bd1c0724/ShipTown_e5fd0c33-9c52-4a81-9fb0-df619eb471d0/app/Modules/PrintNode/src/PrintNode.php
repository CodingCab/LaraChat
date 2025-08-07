<?php

namespace App\Modules\PrintNode\src;

use App\Exceptions\ShippingServiceException;
use App\Modules\PrintNode\src\Models\Client;
use App\Modules\PrintNode\src\Models\PrintJob;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class PrintNode
{
    /**
     * Noop: Quick "ping" to see if communication and authentication works
     */
    public static function noop(?Client $printNodeClient = null): bool
    {
        $client = $printNodeClient ?: self::getFirstPrintNodeClient();

        try {
            $response = $client->getRequest('noop');

            return $response->getStatusCode() === 200;
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function getPrinters(): array
    {
        $printNodeClient = self::getFirstPrintNodeClient();

        if (! $printNodeClient) {
            return [];
        }

        $response = $printNodeClient->getRequest('printers');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @throws ShippingServiceException
     */
    public static function print(PrintJob $printJob): Response
    {
        $printNodeClient = self::getFirstPrintNodeClient();

        if (! $printNodeClient) {
            Log::warning('Print job failed, no PrintNode clients configured');
            throw new ShippingServiceException('PrintNode service not configured');
        }

        return $printNodeClient->postRequest('printjobs', $printJob->toPrintNodePayload());
    }

    public static function getFirstPrintNodeClient(): ?Client
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            return null;
        }

        return $clients->first();
    }
}
