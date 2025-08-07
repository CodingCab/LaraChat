<?php

namespace App\Modules\DpdIreland\src;

use App\Modules\DpdIreland\src\Exceptions\AuthorizationException;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class Client.
 */
class Client
{
    /**
     * DPD LIVE API URL.
     */
    const API_URL_LIVE = 'https://papi.dpd.ie';

    /**
     * DPD Pre Production API URL.
     */
    const API_URL_TEST = 'https://pre-prod-papi.dpd.ie';

    /**
     * Authorization endpoint.
     */
    const COMMON_API_AUTHORIZE = '/common/api/authorize';

    /**
     * PreAdvice API Endpoint.
     */
    const COMMON_API_PREADVICE = '/common/api/preadvice';

    /**
     * Cache key name used for caching authorization.
     */
    const AUTHORIZATION_CACHE_KEY = 'dpd.authorization';

    private static function getBaseUrl(): string
    {
        $config = DpdIreland::firstOrFail();

        return $config->live ? self::API_URL_LIVE : self::API_URL_TEST;
    }

    /**
     * @throws GuzzleException
     * @throws AuthorizationException
     */
    public static function postXml(string $xml): string
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.self::getAuthorizationToken(),
                'Content-Type' => 'application/xml; charset=UTF8',
                'Accept' => 'application/xml',
            ],
            'body' => $xml,
        ];

        Log::debug('API POST REQUEST', [
            'url' => self::COMMON_API_PREADVICE,
            'service' => 'DPD-IRL',
            'request' => str_replace("\n", '', $xml),
        ]);

        $response = self::getGuzzleClient()->post(self::COMMON_API_PREADVICE, $options);
        $response_content = $response->getBody()->getContents();

        Log::debug('API REQUEST', [
            'service' => 'DPD-IRL',
            'response' => $response_content,
            'request' => str_replace("\n", '', $xml),
        ]);

        return $response_content;
    }

    /**
     * @return mixed
     *
     * @throws AuthorizationException|GuzzleException
     */
    private static function getAuthorizationToken()
    {
        $authorizationToken = self::getCachedAuthorization();

        return $authorizationToken['authorization_response']['AccessToken'];
    }

    /**
     * Using cache we will not need to reauthorize every time.
     *
     * @throws AuthorizationException|GuzzleException
     */
    public static function getCachedAuthorization(): array
    {
        $cachedAuthorization = Cache::get(self::AUTHORIZATION_CACHE_KEY);

        if ($cachedAuthorization) {
            $cachedAuthorization['from_cache'] = true;

            return $cachedAuthorization;
        }

        $authorization = self::getAuthorization();

        Cache::put(self::AUTHORIZATION_CACHE_KEY, $authorization, 86400);

        return $authorization;
    }

    /**
     * @throws AuthorizationException|GuzzleException
     */
    private static function getAuthorization(): array
    {
        self::clearCache();

        $config = DpdIreland::firstOrFail();

        $body = [
            'User' => $config->user,
            'Password' => $config->password,
            'Type' => 'CUST',
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$config->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $body,
        ];

        $authorizationResponse = self::getGuzzleClient()->post(self::COMMON_API_AUTHORIZE, $options);

        $response_content = $authorizationResponse->getBody()->getContents();
        $authorization = json_decode($response_content, true);

        if ($authorization['Status'] === 'FAIL') {
            Log::debug('API REQUEST', [
                'service' => 'DPD-IRL',
                'response' => $response_content,
            ]);
            throw new AuthorizationException($authorization['Code'].' : '.$authorization['Reason']);
        }

        return [
            'from_cache' => false,
            'authorization_time' => Carbon::now(),
            'authorization_response' => $authorization,
        ];
    }

    public static function getGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'base_uri' => self::getBaseUrl(),
            'timeout' => 60,
            'exceptions' => false,
        ]);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::AUTHORIZATION_CACHE_KEY);
    }
}
