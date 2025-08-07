<?php

namespace App\Modules\DpdUk\src\Api;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

class CreateShipmentResponse
{
    private Response $apiResponse;

    public function __construct(Response $response)
    {
        $this->apiResponse = $response;
    }

    /**
     * @return mixed
     */
    public function getShipmentId()
    {
        return $this->apiResponse->json('data.shipmentId');
    }

    public function errors(): Collection
    {
        return collect($this->apiResponse->json('error'));
    }

    /**
     * @return mixed
     */
    public function getConsignmentNumber()
    {
        return $this->apiResponse->json('data.consignmentDetail.0.consignmentNumber');
    }

    /**
     * @return mixed
     */
    public function getConsignmentParcelNumber()
    {
        return $this->apiResponse->json('data.consignmentDetail.0.parcelNumbers.0.parcelNumbers');
    }
}
