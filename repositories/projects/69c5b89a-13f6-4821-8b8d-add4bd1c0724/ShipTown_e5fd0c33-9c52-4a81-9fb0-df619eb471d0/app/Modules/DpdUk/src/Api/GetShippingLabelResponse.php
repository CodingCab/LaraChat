<?php

namespace App\Modules\DpdUk\src\Api;

use Illuminate\Http\Client\Response;

class GetShippingLabelResponse
{
    public Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }
}
