<?php

namespace App\Modules\Fakturowo\src\Api;

use Exception;
use Illuminate\Support\Facades\Http;

class FakturowoApi
{
    const RESULT_LINE = 0;
    const ERROR_MESSAGE_LINE = 1;
    const HTML_DOCUMENT_URL_LINE = 3;

    protected static string $defaultApiUrl = 'https://konto.fakturowo.pl/api';

    public static function postInvoice(string $apiKey, $payload, string $apiUrl = null): InvoiceApiResponse
    {
        $apiUrl = $apiUrl ?? self::$defaultApiUrl;
        $payload = array_merge([
            'api_id' => $apiKey,
            'api_zadanie' => '1',
            'dokument_dostep' => '1'
        ], $payload);

        $response = Http::asForm()
            ->timeout(300)
            ->post($apiUrl, $payload);

        if (!$response->successful()) {
            throw new Exception('Fakturowo.pl API request failed.');
        }

        $lines = explode("\n", $response->body());

        $result = trim($lines[self::RESULT_LINE]);

        if ($result !== '1') {
            throw new Exception($lines[self::ERROR_MESSAGE_LINE] ?? 'Unknown error');
        }

        return (new InvoiceApiResponse($lines));
    }
}
