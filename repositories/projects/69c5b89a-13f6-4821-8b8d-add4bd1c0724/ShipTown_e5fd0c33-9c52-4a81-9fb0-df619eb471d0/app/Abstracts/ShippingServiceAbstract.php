<?php

namespace App\Abstracts;

use App\Exceptions\ShippingServiceException;
use App\Models\ShippingService;
use Illuminate\Support\Collection;

abstract class ShippingServiceAbstract
{
    /**
     * @throws ShippingServiceException
     */
    abstract public function ship(int $order_id, ?ShippingService $shippingService = null): Collection;
}
