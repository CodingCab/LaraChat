<?php

namespace App\Modules\Api2cart\src\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

/**
 * Class Orders.
 */
class Orders extends Entity
{
    /**
     * @throws ConnectionException
     */
    public static function get(string $store_key, array $params): ?array
    {
        try {
            $response = Client::GET($store_key, 'order.list.json', $params);
        } catch (ConnectionException $connectException) {
            Log::warning('Failed to connect to API2CART', [
                $connectException->getMessage(),
            ]);

            return null;
        }

        if ($response->isSuccess()) {
            Log::debug('Fetched orders', [
                'source' => 'API2CART',
                'count' => $response->getResult()['orders_count'],
            ]);

            return $response->getResult()['order'];
        }

        Log::alert('Failed API call', [
            'code' => $response->getReturnCode(),
            'message' => $response->getReturnMessage(),
            'params' => $params,
        ]);

        return null;
    }

    /**
     * @throws ConnectionException
     */
    public static function list(string $store_key, array $params): RequestResponse
    {
        return Client::GET($store_key, 'order.list.json', $params);
    }

    /**
     * @throws ConnectionException
     */
    public static function update($store_key, $params): RequestResponse
    {
        return Client::POST($store_key, 'order.update.json', $params);
    }

    /**
     * @throws ConnectionException
     */
    public static function statuses($store_key, $params): RequestResponse
    {
        return Client::GET($store_key, 'order.status.list.json', $params);
    }
}
