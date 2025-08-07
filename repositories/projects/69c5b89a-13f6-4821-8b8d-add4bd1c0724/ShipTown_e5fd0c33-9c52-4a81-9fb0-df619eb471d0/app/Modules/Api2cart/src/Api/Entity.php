<?php

namespace App\Modules\Api2cart\src\Api;

class Entity
{
    private Client $client;

    /**
     * Entity constructor.
     */
    public function __construct(string $store_key, bool $exceptions = true)
    {
        $this->client = new Client($store_key, $exceptions);
    }

    public function client(): Client
    {
        return $this->client;
    }
}
