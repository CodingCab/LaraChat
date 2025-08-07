<?php

namespace App\Modules\ScurriAnpost\src\Api;

use Illuminate\Http\Client\Response;

class Label
{
    public Response $response;

    public array $body;

    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->body = $response->json();
    }

    public function getLabels(): ?string
    {
        return base64_decode($this->body['labels']);
    }
}
