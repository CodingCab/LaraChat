<?php

namespace App\Modules\ScurriAnpost\src\Api;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Class Client
 */
class Client
{
    /**
     * @throws Exception
     */
    private static function authenticatedClient(): PendingRequest
    {
        $username = config('scurri.username');
        $password = config('scurri.password');

        if (in_array(null, [$username, $password])) {
            throw new Exception('Invalid Credentials');
        }

        return Http::withBasicAuth($username, $password);
    }

    private static function fullUrl(string $endpoint): string
    {
        $base_uri = config('scurri.base_uri');
        $company_slug = config('scurri.company_slug');

        return $base_uri.$company_slug.'/'.$endpoint;
    }

    public static function getCarriers(): Response
    {
        return self::GET('carriers');
    }

    public static function getConsignment(string $consignment_id): Response
    {
        return self::GET('consignment/'.$consignment_id);
    }

    public static function createMultipleConsignments(array $data): ?ConsignmentsResponse
    {
        if (empty($data)) {
            return null;
        }

        $response = self::POST('consignments', $data);

        return new ConsignmentsResponse($response);
    }

    public static function getDocuments(string $consignment_id): Response
    {
        return self::GET('consignment/'.$consignment_id.'/documents');
    }

    public static function createSingleConsignment(array $data): string
    {
        $consignmentList = [
            0 => $data,
        ];

        $response = self::createMultipleConsignments($consignmentList);

        if ($response->errors->isNotEmpty()) {
            throw new Exception($response->errors);
        }

        return $response->success[0];
    }

    protected static function GET(string $endpoint): Response|PromiseInterface
    {
        $response = self::authenticatedClient()->get(self::fullUrl($endpoint));

        ray($response->json());

        return $response;
    }

    protected static function POST(string $endpoint, $data): Response|PromiseInterface
    {
        $response = self::authenticatedClient()->post(self::fullUrl($endpoint), $data);

        ray($response->json());

        return $response;
    }
}
