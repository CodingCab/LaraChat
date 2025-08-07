<?php

namespace App\Modules\Api2cart\src\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Client
{
    public static function getCartList(): RequestResponse
    {
        return Client::GET('', 'account.cart.list.json', []);
    }

    /**
     * @throws ConnectionException
     */
    public static function GET(string $store_key, string $uri, array $params): RequestResponse
    {
        $query = array_merge($params, [
            'api_key' => self::getApiKey(),
        ]);

        if (!empty($store_key)) {
            $query = array_merge($query, ['store_key' => $store_key]);
        }

        $httpResponse = Http::baseUrl('https://api.api2cart.com/v1.1/')
            ->timeout(60)
            ->withOptions(['http_errors' => false])
            ->get($uri, $query);

        $response = new RequestResponse($httpResponse->toPsrResponse());

        // hide sensitive information
        $query['api_key'] = '***';
        $query['store_key'] = '***';

        Log::debug('API2CART GET', [
            'success' => $response->isSuccess(),
            'uri' => $uri,
            'response_message' => $response->asArray(),
            'response_code' => $response->getResponseRaw()->getStatusCode(),
            'query' => $query,
        ]);

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    public static function POST(string $store_key, string $uri, array $data): RequestResponse
    {
        $query = [
            'api_key' => self::getApiKey(),
            'store_key' => $store_key,
        ];

        $httpResponse = Http::baseUrl('https://api.api2cart.com/v1.1/')
            ->timeout(60)
            ->withOptions(['http_errors' => false])
            ->withQueryParameters($query)
            ->post($uri, $data);

        $response = new RequestResponse($httpResponse->toPsrResponse());

        // hide sensitive information
        $query['api_key'] = '***';
        $query['store_key'] = '***';

        Log::debug('API2CART POST', [
            'success' => $response->isSuccess(),
            'uri' => $uri,
            'response_message' => $response->asArray(),
            'response_code' => $response->getResponseRaw()->getStatusCode(),
            'query' => $query,
            'json' => $data,
        ]);

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    public static function DELETE(string $store_key, string $uri, array $params): RequestResponse
    {
        $query = array_merge($params, [
            'api_key' => self::getApiKey(),
            'store_key' => $store_key,
        ]);

        $httpResponse = Http::baseUrl('https://api.api2cart.com/v1.1/')
            ->timeout(60)
            ->withOptions(['http_errors' => false])
            ->delete($uri, $query);

        $response = new RequestResponse($httpResponse->toPsrResponse());

        // hide sensitive information
        $query['api_key'] = '***';
        $query['store_key'] = '***';

        Log::debug('API2CART DELETE', [
            'success' => $response->isSuccess(),
            'uri' => $uri,
            'response_message' => $response->asArray(),
            'response_code' => $response->getResponseRaw()->getStatusCode(),
            'query' => $query,
        ]);

        return $response;
    }


    public static function getApiKey(): string
    {
        return config('app.api2cart_api_key');
    }
}
