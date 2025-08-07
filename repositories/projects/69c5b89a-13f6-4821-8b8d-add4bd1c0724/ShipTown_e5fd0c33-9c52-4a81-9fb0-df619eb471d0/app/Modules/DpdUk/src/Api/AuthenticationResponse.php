<?php

namespace App\Modules\DpdUk\src\Api;

use Illuminate\Http\Client\Response;

class AuthenticationResponse
{
    public Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getGeoSession(): string
    {
        return $this->response->json('data.geoSession');
    }
}
