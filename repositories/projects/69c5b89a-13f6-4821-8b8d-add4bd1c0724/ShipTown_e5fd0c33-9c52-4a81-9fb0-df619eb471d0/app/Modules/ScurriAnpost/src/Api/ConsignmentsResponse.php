<?php

namespace App\Modules\ScurriAnpost\src\Api;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class ConsignmentsResponse
{
    private Response $response;

    public array $body;

    public Collection $errors;

    public Collection $success;

    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->body = $response->json();
        $this->errors = collect($this->body['errors']);
        $this->success = collect($this->body['success']);
    }

    public function consignmentId()
    {
        return $this->response->json()['success'][0];
    }
}
